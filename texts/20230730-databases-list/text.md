- slug:databases-reading-list
- date:Jul 30, 2023 18:08

# Databases Design Reading List

Once I dived in the world of storage engines (that power modern DBMSes) I instantly discovered a huge hole in
theoretical and practical knowledge in this area. Compared to my usual work profile related to web-services, where a
database is abstracted behind simple API like SQL, database internals seem foreign but quite exciting, sparking
imagination, so to speak.

If we are to study how databases work we can start as low as file I/O and related system calls, which leads us to
refreshing how operating systems work and how to optimize I/O. DB engines deal with quite different data payload, often
huge payload, so learning how to represent data in memory for optimal read or write is crucial. Fundamental algorithms
for dealing with datastructures (memory-resident or disk-resident) is an important step on the way.

At this point I am mostly interested in low-level ideas powering database engines, the ones that manage memory and disk
I/O. I believe I will expand my interest further as I learn foundational pieces. Here is my personal reading list that I
use for writing data storage related programs.

- Books
    - [Advanced Programming in the Unix Environment](https://www.amazon.com/Advanced-Programming-UNIX-Environment-3rd/dp/0321637739)
      To master data manipulation, I need to know how OSes abstract disks and which means for accessing disks
      efficiently exist. This books gives great overview of all foundational I/O related tools and concepts.
    - [Art of Computer Programming, The: Sorting and Searching, Volume 3](https://www.amazon.com/Art-Computer-Programming-Sorting-Searching/dp/0201896850)
      Here the famous pr.Knuth outlines all the foundational ideas behind efficient work with data. I personally liked
      the overview of in-memory and disk-resident data (external sorting) algorithms.
    - [Database Internals: A Deep Dive into How Distributed Data Systems Work](https://www.amazon.com/Database-Internals-Deep-Distributed-Systems/dp/1492040347/ref=sr_1_1?keywords=database+internals&sr=8-1)
      Alex Petrov does a great job explaining in-depth how popular database engine work with data (including flavours of
      B-trees, LSM trees and more). Not only theoretical concepts, but file layouts.
    - [Storage Systems Organization, Performance, Coding, Reliability, and Their Data Processing](https://www.amazon.com/Storage-Systems-Organization-Performance-Reliability/dp/0323907962)
      Huge historical encyclopedia of various storage technologies, their history and reasons behind. Quite a gem.
    - [Algorithms and Data Structures for External Memory](https://www.nowpublishers.com/article/Details/TCS-014)
      I am particularly interested in data engines that manage data that greatly exceeds computer RAM. This book gives
      great overview of ideas and developments in this area.
    - [Introduction to Information Retrieval](https://www.amazon.com/Introduction-Information-Retrieval-Christopher-Manning/dp/0521865719)
      Good overview of topics related to data manipulation.
- Papers
    - [B-tries for disk-based string management](https://people.eng.unimelb.edu.au/jzobel/fulltext/vldbj09.pdf) When a
      dictionary is huge, we need ways to compress and work with it in pieces.
    - [Inverted Files for Text Search Engines](https://dl.acm.org/doi/10.1145/1132956.1132959) Text indexing is mostly
      based on building inverted indexes. This is a nice overview of the area.
    - [GLIMPSE: A Tool to Search Through Entire File Systems](https://dl.acm.org/doi/10.5555/1267074.1267078) I am
      interested in designing a search for text files. Glimpse is a small program with great ideas to study. Efficient
      and innovative at the time.

The list evolves as I read new things and find them helpful.