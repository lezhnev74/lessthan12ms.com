- date: 13 Aug 2023, 12:00
- slug: inverted-index

# Designing Inverted Index

An inverted index is a powerful and widely adopted concept used in most text search systems. It is built on top of text
documents for fast searching. In its essence, an inverted index is a map where keys are terms and values are arrays of
document ids that contain the terms. In Go that would be `map[string][]int`. Whenever we index a new document and
extract terms from it we update the index. Simple.

There are a lot of variations in the design and implementation of such indexes. All of those are designed to address
different problems and thus support different sets of features. To name a few: relevance of documents, matches
highlighting, look-alike search, and more. Some are designed to be resident in the main memory, while others aim to be
memory-efficient and reside in secondary storage. Some are for immutable documents others are for documents that change
often. As you can see there are a lot of moving parts which makes it an inverted index, to design one I have to specify
my problems and make design choices accordingly.

## Requirements For Inverted Index

My program searches in log files. That alone gives me a few insights. Firstly, log files are append-only. Every document
in the log is immutable. Secondly, I am only interested in exact search, so if I look for "error1" I want only documents
that contain this keyword as a whole. There is no other search relevancy besides that. Thirdly, documents in log files
are never removed except in the case when the whole log file is removed, so I need no API to remove a document from the
index if I make one on a per-file basis. No removal simplifies index design. Notably, log files are constantly being
written to, we can think of it as a stream of new documents flying in constantly, so the index must not "wait" for the
complete set of documents and rather be okay working with an unbounded stream.

To continue writing out my requirements, I want this program to be small in terms of disk space and memory usage. It is
a kind of program that resides on the server and indexes local log files continuously. Searches are not often and it is
tolerable for them to be somewhat slow if the above requirements are met.

As you can see, I am describing high-level requirements and the program is not only about the inverted index, however,
an index is at the heart of the program and impacts all aspects of it, including memory/disk consumption, speed of
ingestion, and reads and overall responsiveness of the program as the index grows.

## Design Decisions

### Memory Efficiency

What I want is predictable memory usage even for growing indexes. For that purpose, the index must be disk-resident with
the ability to read its parts to the memory to search. Writing should also be predictable in terms of memory even for a
big input set of documents. Thus it must split incoming data into pieces and flush to secondary storage frequently.

### Secondary Storage Efficiency

I am trying to abstract myself of the storage type, it could be a disk or SSD. Either way, the taken space should be
minimized where possible as the input can be huge (think of hundreds of gigabytes of log files). For that purpose, we
need to compress data as we write it out.

Speaking of I/O efficiency. Since the index will never be fully loaded to the main memory, we have to read and write
parts of it. We know that I/O is expensive (especially for disks), so minimizing it would be a priority. To optimize for
writes we can go with immutable index parts. Whenever we add something to the index we do appending and never modify.
That is cheap as the write is sequential, also it is good as we don't do any reading before writing. To optimize for
reads we have to read only the fewest pieces possible to serve the search query. To do that we have to put data in small
chunks, so we can read only a few of them when needed instead of reading everything.

Lastly, speaking of the storage, I opted out of removal support. Whenever the log file is deleted I remove the whole
index associated with it. Thus an index per file is a compromise.

### Algorithms And Data Structures

Having specified the high-level requirements, I can think about what technology can be used to support them. Here I am
climbing down the abstraction ladder and coming closer to implementation details. Still, I am not going to talk code in
this post, but choosing fundamental ideas for the job here makes sense and will impact the code and the file format for
the index as we will see below.

A classic and simple inverted index is used to find documents that contain a term. To spice it up a little, I want to
add one little feature to it. If I replace document ids with document timestamps in the index, I will be able to make
searches not only based on terms but also by date ranges. For example, think of a query like that "find all today's docs
that contain 'error'". Yes, that would need another mapping layer from timestamps to docs, but that is a simple problem
to address. Now I can use this inverted index as a "2 column" index (term, timestamp). Both help to narrow down pieces
to read from storage into memory.

The inverted index compression is a known problem. It has been thoroughly studied. There are two parts of the index:
terms which are strings (or bytes in general) and document timestamps which are integers. Both can be efficiently
compressed. For integers, there are plenty of algorithms to choose from. As an entry point, I
like [Daniel Lemire's work](https://lemire.me/blog/2012/09/12/fast-integer-compression-decoding-billions-of-integers-per-second/).

For terms(strings) compression is a bit trickier. I will not share my thoughts and ideas about possible solutions. I
will just post here the technology that Lucene (and many other engines) uses for this purpose - FST (Finite State
Transducers). There is a perfect easy-to-read introduction here
by [Andrew Gallant](https://blog.burntsushi.net/transducers/). Shortly it is a tree where strings share prefixes and
suffixes and thus preserve space.

## Index API

To specify what an index can do in terms of client API, let me outline the public methods (in Go).

- `index.Put(term string, timestamps []int) error` this method must accept terms in sorted order otherwise FST will
  return an error. It writes values to disk as soon as they come, FST writing is delayed until the index is closed.
- `index.ReadTerms() iterator, error` read-only terms from FST index (faster as FST is stored separately from values)
- `index.ReadTimestamps(terms []string, min int, max int): iterator, error` read values only. To minimize I/O provide
  min/max boundaries of the timestamps.
- `index.Close() error` write out FST (if in write mode) and close the index.

A few words about intended API usage. Usually, the indexer crawls documents and extracts terms in batches. It
accumulates terms for N documents. Once the batch is parsed in memory, we can stream it to the index by
calling `index.Put(term, timestamps)`. Thus the size of the index file is controlled by the outside indexer, and our
index implementation does not have any intermediary buffer to accumulate values (except the FST that must be finalized
after we write out all the values to disk). It will write all it is given.

## File Format

Here I am going to talk about file format that will help us solve I/O problems and minimize the memory needed for
searches. If we look again at the index data, that is a map `map[string][]int`. Where keys are terms and for each key,
there is an array of integers(timestamps). Some searches require only terms, some also require reading values. It
resembles the idea of covering compound indexes in relational databases.

Firstly, let's address the terms part. FST is a great idea to store and query sorted strings, both disk and memory
efficient. It allows to store a map where keys are strings and values are single integers. We can use it to save file
offsets for values related to each term: `map[term]fileOffset`. Interestingly, we can only know offsets of values after
we wrote them out, so this FST structure would be at the end of our index file:

```
[......values.....|fst|fst_len]
```

`fst_len` would show how to find the fst segment in the file. So reading FST will involve two steps:

1. read the last N bytes to detect the size of the FST.
2. seek to the FST position and start reading it.

Now to search only through terms we only need to read the tail of the file.

Secondly, let's talk about term values (timestamps). For each term in the index, we have a list of integers. The order
of integers is not important for the program, so we can sort it and apply compression to it efficiently. The file format
would look like this:

```
[term1_compressed_values|...|termN_compressed_values|fst|fst_len]
```

Now this is a nice file format that can be written in one run and allows efficient searching through terms and if needed
returning values too. But it has a flaw. If we are only interested in a subset of values (min, max boundaries provided)
we still need to read all of them, decompress, and only return matching items. This is unnecessary I/O (though
sequential) that we want to avoid. For smaller indexes that is fine and a full scan can be OK, but for big files that is
not a good idea.

What we can do is split a list of each term's values into sorted segments and compress them separately, like this:

```
(1,2,3), (4,5,6), ..., (98,99,100)
```

Thus if we want to find values between 0 and 5, we only need to read the first two segments. Sounds good. But how do we
know the offsets of each segment? There are probably different solutions, but I decided to go with placing a small index
before each term's values. An index is a simple sorted array of min values in segments and offsets. Thus such an index
is small and can be loaded into memory in one read and can be used to do a binary search of segments that match the
search query. This can effectively reduce the number of I/O.

Now the overall file structure would look like this (let me put each piece on a new line for visibility):

```
[
term1_values_index_len
term1_values_index
term1_compressed_values_segment1
...
term1_compressed_values_segmentN
...
termP_values_index_len
termP_values_index
termP_compressed_values_segment1
...
termP_compressed_values_segmentM
fst
fst_len
]
```

The above file structure satisfies my requirements. Let's reiterate its benefits:

1. It can be written in one pass (values come first, afterward FST is being written)
2. Searching terms is cheap as it only requires reading FST from the file
3. Searching values generally requires loading all file contents, decompressing, and filtering. But with the segmenting,
   we can reduce the number of read segments.
4. All terms and values are sorted, so we can later apply efficient merging of results from different indexes. Or merge
   indexes in general.

## Wrapping Up

Wow. I did not expect you to make it this far. Thank you for your attention and interest. The actual index
implementation and code will be published in a GitHub repository and a dedicated blog post will follow.

Ok, wrapping up. The above text was intentionally scoped only to inverted index as a unit. The search program is much
more than that. Notably, an indexer will produce many inverted indexes that we want to query concurrently, merge for
removing duplicates and remove once the source data file is removed. That is also something left out for future texts.

## Implementation And Code

I implemented the ideas described in this text as a Go program. See the code here: [github.com/lezhnev74/inverted_index](https://github.com/lezhnev74/inverted_index).

Let me know what you think.
