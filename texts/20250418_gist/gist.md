- date:Apr 18, 2025 12:00
- slug:pg_trgm_gist
# Faster Text Search With PostgreSQL's GiST Index And Trigrams.


## GiST Index
GiST stands for Generalized Search Tree. We like tree structures for their quick data look-ups (usually with logarithmic time). GiST allows using a tree structure to keep various data types, hence "generalized". It has a generic algorithm and extension points, a few functions that each operator class must implement. This is a brilliant idea.

For example, B-trees are optimized for sortable data. GiST allows data to be indexed with customization logic that spreads values across the tree without regard to order. It applies to geospatial data(points in space), ranges (time), bitmaps (text values based on trigrams or lexemes), and more.

## Extension [pg_trgm](https://www.postgresql.org/docs/current/pgtrgm.html)
One of the convenient ways of indexing texts is splitting it into trigrams.
```sql
select show_trgm('Apple iPhone');


{  a,  i, ap, ip,app,hon,iph,le ,ne ,one,pho,ple,ppl}
```
The original text splits into 3-character sequences, and the index uses them as keys for each indexed row. This sort of search is not exact and needs re-checking after index look-ups because trigrams lose details of the original text. The [pg_trgm extension](https://www.postgresql.org/docs/current/pgtrgm.html) brings the trigram type to the database along with the operations required to use this type in a gist index.

### Trigram Operators

- `similarity ( text, text ) → real`: Returns a number that indicates how similar the two arguments are. The range of the result is zero (indicating that the two strings are completely dissimilar) to one (indicating that the two strings are identical).
- `text <-> text → real`: returns the “distance” between the arguments, that is one minus the similarity() value.

This is the first quite useful group of operators that we may need for text search. It calculates the distance between the two sets of trigrams, the higher the score the more the sets are alike.

```sql
select
    similarity('Apple iPhone', 'SAMSUNG Galaxy S25 Ultra Cell Phone') as similarity_1,
    similarity('Apple iPhone', 'Apple Pro iPhone SE 3rd Gen') as similarity_2
;

+------------+------------+
|similarity_1|similarity_2|
+------------+------------+
|0.09090909  |0.4642857   |
+------------+------------+
```

See, that our initial text has much higher similarity score with the second option, that is so because there is a bigger intersection of trigrams.

The next operation is more precise and calculates the trigram intersection for each word in the right argument.
- `word_similarity ( text, text ) → real`: Returns a number that indicates the greatest similarity between the set of trigrams in the first string and any continuous extent of an ordered set of trigrams in the second string.
- `text <<-> text → real`: Returns the “distance” between the arguments, that is one minus the word_similarity() value.

```sql
select
    similarity('Apple iPhone', 'SAMSUNG Galaxy S25 Ultra Cell Phone') as similarity_1,
    similarity('Apple iPhone', 'Apple Pro iPhone SE 3rd Gen') as similarity_2,
    word_similarity('Apple iPhone', 'SAMSUNG Galaxy S25 Ultra Cell Phone') as word_similarity_1,
    word_similarity('Apple iPhone', 'Apple Pro iPhone SE 3rd Gen') as word_similarity_2
;

+------------+------------+-----------------+-----------------+
|similarity_1|similarity_2|word_similarity_1|word_similarity_2|
+------------+------------+-----------------+-----------------+
|0.09090909  |0.5416667   |0.30769232       |0.7647059        |
+------------+------------+-----------------+-----------------+
```

See that `word_similarity_2` gave us a higher score for the "apple" title. That is because it tried to find the shortest sequence of trigrams in the right argument that gives the biggest similarity score. In our case that would be `Apple Pro iPhone`, let's confirm:

```sql
select
    word_similarity('Apple iPhone', 'Apple Pro iPhone') as word_similarity_1,
    word_similarity('Apple iPhone', 'Apple Pro iPhone SE 3rd Gen') as word_similarity_2
;

+-----------------+-----------------+
|word_similarity_1|word_similarity_2|
+-----------------+-----------------+
|0.7647059        |0.7647059        |
+-----------------+-----------------+
```

There is a third operation that brings even more precision to our matching.
- `strict_word_similarity ( text, text ) → real`: Same as word_similarity, but forces extent boundaries to match word boundaries. Since we don't have cross-word trigrams, this function actually returns the greatest similarity between the first string and any continuous extent of words of the second string.
- `text <<<-> text → real`: Returns the “distance” between the arguments, that is one minus the strict_word_similarity() value.

This operation also tries to find the shortest sequence of trigrams, but it keeps word boundaries. Let's learn from the example:
```sql
select
    word_similarity('Apple iPhone', 'Apple iPhone12 SE') as word_similarity_1,
    strict_word_similarity('Apple iPhone', 'Apple iPhone12 SE') as word_similarity_2
;

+-----------------+-----------------+
|word_similarity_1|word_similarity_2|
+-----------------+-----------------+
|0.9230769        |0.75             |
+-----------------+-----------------+
```

Here `word_similarity` gave a bigger score because it found the shortest part of the text which was `Apple iPhone`, while `strict_word_similarity` used `Apple iPhone12`. I hope you noticed that it gave `0.92` instead of `1` even though two substrings seem equal: `Apple iPhone12` and `Apple iPhone12`. That is because it does not compare strings, but compares trigram sets which are not exactly equal:
```sql
select
    show_trgm('Apple iPhone') as set_1,
    show_trgm('Apple iPhone12') as set_2
;

Transposed results:
+-----+-------------------------------------------------------------+
|set_1|{  a,  i, ap, ip,app,hon,iph,le ,ne ,one,pho,ple,ppl}        |
+-----+-------------------------------------------------------------+
|set_2|{  a,  i, ap, ip,12 ,app,e12,hon,iph,le ,ne1,one,pho,ple,ppl}|
+-----+-------------------------------------------------------------+
```

Now let's try to use the trigrams search for a misspelled input like `aple pone`.
```sql
select
    similarity('aple pone', 'SAMSUNG Galaxy S25 Ultra Cell Phone') as similarity_1,
    similarity('aple pone', 'Apple Pro iPhone SE 3rd Gen') as similarity_2,
    word_similarity('aple pone', 'SAMSUNG Galaxy S25 Ultra Cell Phone') as word_similarity_1,
    word_similarity('aple pone', 'Apple Pro iPhone SE 3rd Gen') as word_similarity_2,
    strict_word_similarity('aple pone', 'SAMSUNG Galaxy S25 Ultra Cell Phone') as strict_word_similarity_1,
    strict_word_similarity('aple pone', 'Apple Pro iPhone SE 3rd Gen') as strict_word_similarity_2
;

+------------+------------+-----------------+-----------------+------------------------+------------------------+
|similarity_1|similarity_2|word_similarity_1|word_similarity_2|strict_word_similarity_1|strict_word_similarity_2|
+------------+------------+-----------------+-----------------+------------------------+------------------------+
|0.071428575 |0.22580644  |0.23076923       |0.41666666       |0.23076923              |0.35                    |
+------------+------------+-----------------+-----------------+------------------------+------------------------+
```

We can see that it gives a better score for the obviously better match. Trigrams offer great help when we want to allow misspelled words in our input. We will learn the cost of such fuzziness. Having the big picture of how trigram operations work, we can learn how the GiST index uses them to make faster searches, and when the index can't be used (surprise, surprise).


### How Trigrams Are Used In the GiST Index?

In a gist index, big trigram sets are compressed into bitmaps (or signatures) or a fixed length, where each trigram is hashed to a single bit set to "1" in such a bitmap. Since there are vast amounts of possible trigrams and just a fixed length bitmap, multiple trigrams may end up setting the same bit in the map. This is where we lose precision, so to speak, and this is why re-checking is required for each potentially matched string.

The Gist index supports common operators `LIKE`/`ILIKE` which work very fast as they convert all symbols except `%` in their arguments to trigrams. And then look for rows whose signatures contain all of the bits set for each trigram. Given so short signature length this ends up in scanning very few rows.

Also, we need to use special trigram operators in the query in order to use the index. Because functions like `similarity()` are calculated after the fetch of rows from the heap:
- `text % text → boolean`: Returns true if its arguments have a similarity that is greater than the current similarity threshold set by `pg_trgm.similarity_threshold` (defaults to 0.3).
- `text <% text → boolean`: Returns true if the similarity between the trigram set in the first argument and a continuous extent of an ordered trigram set in the second argument is greater than the current word similarity threshold set by `pg_trgm.word_similarity_threshold` parameter (defaults to 0.5).
- `text <<% text → boolean`: Returns true if its second argument has a continuous extent of an ordered trigram set that matches word boundaries, and its similarity to the trigram set of the first argument is greater than the current strict word similarity threshold set by the `pg_trgm.strict_word_similarity_threshold` parameter (defaults to 0.6).

Let's see some results on the 2M rows data set, containing some amazon product reviews:
```sql
explain(analyze, costs off) select * from reviews where review_text like '%iPhone%';

+--------------------------------------------------------------------------------------+
|QUERY PLAN                                                                            |
+--------------------------------------------------------------------------------------+
|Bitmap Heap Scan on reviews (actual time=13.014..15.041 rows=362 loops=1)             |
|  Recheck Cond: (review_text ~~ '%iPhone%'::text)                                     |
|  Rows Removed by Index Recheck: 194                                                  |
|  Heap Blocks: exact=541                                                              |
|  ->  Bitmap Index Scan on reviews_trgm1 (actual time=12.938..12.938 rows=556 loops=1)|
|        Index Cond: (review_text ~~ '%iPhone%'::text)                                 |
|Planning Time: 0.115 ms                                                               |
|Execution Time: 15.072 ms                                                             |
+--------------------------------------------------------------------------------------+

explain(analyze, costs off) select * from reviews where 'iPhone' % review_text;

+--------------------------------------------------------------------------------------+
|QUERY PLAN                                                                            |
+--------------------------------------------------------------------------------------+
|Bitmap Heap Scan on reviews (actual time=810.100..810.101 rows=0 loops=1)             |
|  Filter: ('iPhone'::text % review_text)                                              |
|  ->  Bitmap Index Scan on reviews_trgm1 (actual time=810.094..810.094 rows=0 loops=1)|
|        Index Cond: (review_text % 'iPhone'::text)                                    |
|Planning Time: 2.939 ms                                                               |
|Execution Time: 810.123 ms                                                            |
+--------------------------------------------------------------------------------------+

explain(analyze, costs off) select * from reviews where 'iPhone' <% review_text;

+-----------------------------------------------------------------------------------------+
|QUERY PLAN                                                                               |
+-----------------------------------------------------------------------------------------+
|Bitmap Heap Scan on reviews (actual time=302.269..1127.537 rows=1005 loops=1)            |
|  Filter: ('iPhone'::text <% review_text)                                                |
|  Rows Removed by Filter: 4880                                                           |
|  Heap Blocks: exact=5529                                                                |
|  ->  Bitmap Index Scan on reviews_trgm1 (actual time=301.557..301.557 rows=5885 loops=1)|
|        Index Cond: (review_text %> 'iPhone'::text)                                      |
|Planning Time: 2.935 ms                                                                  |
|Execution Time: 1127.633 ms                                                              |
+-----------------------------------------------------------------------------------------+

explain(analyze, costs off) select * from reviews where 'iPhone' <<% review_text;

+------------------------------------------------------------------------------------------+
|QUERY PLAN                                                                                |
+------------------------------------------------------------------------------------------+
|Bitmap Heap Scan on reviews (actual time=626.357..4753.254 rows=666 loops=1)              |
|  Filter: ('iPhone'::text <<% review_text)                                                |
|  Rows Removed by Filter: 24466                                                           |
|  Heap Blocks: exact=19199                                                                |
|  ->  Bitmap Index Scan on reviews_trgm1 (actual time=623.627..623.628 rows=25132 loops=1)|
|        Index Cond: (review_text %>> 'iPhone'::text)                                      |
|Planning Time: 4.641 ms                                                                   |
|Execution Time: 4753.395 ms                                                               |
+------------------------------------------------------------------------------------------+
```
Note that all the operations used the index (see `Index Scan` in the query explain output), and each operator took a different execution time.

### Why LIKE/ILIKE Is Much Faster Than `%` operator?
The fastest was `LIKE` as it quickly discarded any signatures where at least one trigram was missing and benefited from the index the most (see [real-world example](https://ferfebles.github.io/2018/04/16/Improving-large-Drupal-Postgres-performance-by-using-pg_trgm.html)). However, being the fastest, LIKE does not allow fuzziness, compare the number of rows returned:

```sql
select count(*) from reviews where review_text like '%iPhone%';

+-----+
|count|
+-----+
|362  |
+-----+


select count(*) from reviews where review_text like '%iPone%';

+-----+
|count|
+-----+
|0    |
+-----+
```

We get speed by giving up fuzziness. Fairly speaking, the best performance could be achieved from b-tree index, but it only works for postfix wildcards: `... WHERE text LIKE 'word%'` and can't be used for queries like `... WHERE text LIKE '%word%other%word%'`. So it is up to you to decide what you need the most.

### Will `LIMIT` improve latency?
In fact, using `LIMIT` operator may greatly improve the speed of your queries as it stops traversing the tree after finding X matching rows. The caveat there is that the GiST index is not sorted, so the search will return the first matching rows but does not guarantee that this is the best match. To select the best matching rows we have to check them all, calculate the score for each one, sort the set, and return the first X rows. So it could be an option, but is it worth it?

### `Siglen` value for the index.
Siglen - is the size of a signature type, that GiST uses for hashing trigram sets. The bigger the length, the more space the index takes but the faster searches we get. By default it uses 12 bytes:

```sql
CREATE INDEX trgm_idx ON reviews USING GIST (review_text gist_trgm_ops(siglen=32));
```

## Strategies For Gist Searches

### "Exact" Searching Strategy based on ILIKE.
As we have seen above, `LIKE`/`ILIKE` operators give the best latency, but does not support fuzziness.
It works fast no matter where the wildcard `%` symbol is used, which makes it better than b-tree indexes.
Verdict: good for some cases where strict match is required, not suitable for general search.

### Strategy "%".
Based purely on trigram similarity. Usually, the input query is quite small like `iPhone` and indexed texts can be huge like a product description. In these circumstances, the similarity score will always be small and noisy (not easy to tell if the text really matches the query).
Verdict: good for text-to-text searching, not good for general search.

### Strategy "<%" or "<<%".
With a shorter query and a longer text, operators like `<%` or `<<%` are much better. They give good scores only to texts that contain the query in a sequential fragment. This is by far one of the best results that we can get from gist based on trigrams, however, it casts more latency.
Verdict: good for general search, but needs tweaking of siglen and maybe partitioning data for better parallel searches to improve latency.

### "2-Queries" Strategy
Here we go to a combined strategy that can give the best user experience(low latency) while supporting fuzziness.
We do 2 concurrent queries: one uses `LIKE`/`ILIKE` operator, and the other one uses `<<%` operator.
Obviously, the first one will return results much faster and maybe there will be enough for the customer's needs. In the case of mismatch, the user will have to wait for the finishing of the second query which will allow misspells and fuzzy search.
Verdict: a good combination of queries to offer a great user experience.


In another text, I hope to cover the GiN index type offered by PostgreSQL and how it makes text search faster.

## Refs:
- [pg_trgm extension](https://www.postgresql.org/docs/current/pgtrgm.html)
- [pg_trgm extension source code](https://github.com/postgres/postgres/tree/master/contrib/pg_trgm)
- [gist source code](https://github.com/postgres/postgres/tree/master/src/backend/access/gist)
- [Optimizing Postgres Text Search with Trigrams](https://alexklibisz.com/2022/02/18/optimizing-postgres-trigram-search)
