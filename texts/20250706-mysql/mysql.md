- date:July 8, 2025 12:00
- slug:cheap-mysql
# How to Run MySQL in Production for Under &dollar;100/month

**TL;DR**: Start with a single MySQL instance on a redundant VPS with backups. Move to a replica when read traffic or
uptime becomes an issue. For high availability, MySQL's InnoDB Cluster offers multi-primary setup with automatic
failover—but
requires more ops skill and cost.

Many projects start their journey with managed cloud solutions to power their databases.
A couple of buttons clicked and it works. With such simplicity there is a downside - cost.
Bills for managed databases will scale up much faster than your company's revenue.
There are ways to keep things cheap yet reliable using simple virtual machines that cost next to nothing.

Here we will look at deployment strategies for cheap MySQL deployments that will
survive crashes of cheap hardware in automatic or manual fashion.
We will discuss options of topologies that are sufficient to meet your company requirements
without incurring additional costs. We buy simplicity and easiness for money, so to save money we have to increase
complexity and
slightly more laborious. From your end it will require certain understanding of MySQL architecture,
backup and recovery methods so you can manage your database fleet with confidence.

## Key Considerations

How fast the failed db must recover (**RTO** - recovery time objective).
In many early stage projects 99.999% uptime is not needed. Few customers do few things and there’s no significant harm
if the website goes down for a few minutes with a nice picture like "be back shortly". However to some ambitious
startups that need to be online 24/7 even small downtime drives customers away.

How much data is tolerable for losing (formally **RPO** - recovery point objective).
Very few companies would say "it's ok to lose data", right? So our strategies should show a path to no-data-loss
setup. And in fact, this is achievable with the current toolset available for MySQL.

How to recover data - automatic or manual flow.
Depending on the above considerations, manual recovery can be acceptable. And a lot of companies in fact live like that.
Automated failover is a feature that needs more work, but worth it if the business must serve customers throughout
the clock.

This is how RPO and RTO can be pictured on the time axis.

```
              ______    _______
             / RPO  \ /   RTO  \
+-----------B--------F----------R-----> time
            |        |          |
  Last Backup taken  |          |
                     |          Recovery completed
                     Failed state detected
```

## Single Instance + Stock disk (Cheapest)

The simplest idea is to run a single MySQL container on a cheap VPS (~$20/month for 2CPU/4G-RAM), mount `/var/lib/mysql`
to a local folder for data persistency and that's it. In case the server dies
because of OOM (out of memory) or technical procedure done by the
hoster, the database will instantly (or gracefully) shutdown and recover automatically next time the container is up.
MySQL has built-in mechanisms to survive sudden power loss or OOM events. In this scenario everything depends on the
quality
of the disk that accommodates `/var/lib/mysql` folder.

### (Un)Reliable Disks

Many cloud VPS providers offer reliable and redundant block storage that is protected from hardware failure (~10$/month
for a 100G storage). That could be quite enough for the start of your company. At least we know our data won’t be lost (
the faulty disk is automatically detached and another copy promoted for work).

It could be surprising, but many websites have used this deployment option for years. Let's see what to expect from
this:

- recovery time depends on how fast the server can be restarted. If the hoster takes an hour for maintenance - your
  database is down for the same amount of time.
- data loss can be total if the underlying disk has no backup policy on the infrastructure level, or the hardware disk
  has no batteries. A simple power outage or fire could destroy the disk — and your data with it.
- mostly, the recovery is happening in automatic mode as it only requires to re-start the server instance.

However secure disks will only protect your data from hardware crashes. It won't help you if the malfunctioning app code
erased your tables. And from my experience application-level errors are a more frequent cause of data loss than hardware
failures. To add an extra layer of protection we could do regular backups. Backups enable point-in-time recovery, which
prevents accidental data loss by legitimate operations.

## Two Instances (Primary + Replica) (Manual Failover)

Double instances located on different VPSes give us interesting benefits. First of all, we can scale reads, writes
always go to the primary server, while reads can go to both. That can offload the main server and it is a good
idea to read reports and analytics from the replica (services like Metabase). The second benefit is fault tolerance. If
our main server goes down for long, we can promote the replica to primary. Promotion is done manually so the delay can
be up to minutes,
depends on your database operator. Also, backups can be taken from the replica to reduce load on the primary server.

### MySQL Replicaset

MySQL offers a special product called ReplicaSet. In a replicaset there is one primary instance that accepts all
writes, and one or more replicas that asynchronously get updated (at the best effort). Reads can be done from either
instance. Now, one important detail about the Replicaset. Each replica read the binlog from the primary instance using
separate threads. This means the primary doesn’t wait for a replica’s acknowledgment before committing a transaction.
So in the case of a crash, the replica can be missing a number of the most recent transactions that it did not backup
yet.

MySQL offers a simple tool to manage multi-server deployments - `mysqlshell`. The tool greatly reduces the entry
barrier for the database management. A replicaset with 2 servers can be set up in just a few minutes.

### Semi-Synchronous Replication

To avoid a possibility of losing data, a replica should acknowledge each transaction before the primary commits it. This
replication is called semi-synchronous because the replica persists the change in the local file, but does not apply
it before acknowledging (as compared to synchronous where replicas must apply the change before acknowledging).

In MySQL this replication method is available as a standalone plugin that must be installed and configured. A bit
more work, but done once, after which the cluster runs as normal.

## Three+ Primary Instances (Automatic Failover)

The most advanced option for a startup that assumes high traffic and can't afford to lose any data. We run many
instances each can accept writes and reads. The replication is synchronous (called Group Replication in MySQL),
so a majority of instances must confirm every write each time. This solution heavily depends on quality networks,
you'll likely need to choose a cloud provider with high-quality networking.

MySQL offers "InnoDB Cluster" product which is a good choice for this case. The setup allows to do automatic
failover so even one node crashes, the cluster continues to function without human intervention. By the time your
company needs this, you'll likely have a skilled database operator to manage it.

```
| Topology                                           | Cost | Recovery Method | Recovery Time | Data Loss Risk |
|----------------------------------------------------|------|-----------------|---------------|----------------|
| Single VPS                                         | ~$20 | Server restart  | Hours/Days    | High           |
| Single VPS + Reliable Disk                         | ~$30 | Server restart  | Hours         | Medium         |
| Single VPS + Reliable Disk + Backups + Binlog Sync | ~$30 | Server restart  | Hours         | Low            |
| Primary + Replicas (Async)                         | ~$60 | Manual          | Minutes/Hours | Low            |
| Primary + Replicas (Semi-Async)                    | ~$60 | Manual          | Minutes/Hours | No             |
| 3+ Primaries (Sync)                                | ~$90 | Automatic       | Milliseconds  | No             |
```

## Backup Types & No-Data-Loss Techniques

Backup is a process of copying database data to a remote location that is not seen or affected by the users of the
database. Backup process itself can impact database performance — especially if you're running a single instance. There
are two major ways of doing backups: logical backup and physical backup of your data.

### Logical Backup

Quite a popular choice for early stages of projects due to its simplicity. During the logical backup the tool
(`mysqldump` is the default tool for that, but many other exists like `mydumper`) connects to the database as a normal
user, reads all the data and converts it to some format (usually to SQL statements, but could be JSON or CSV).

It is simple, easy-to-understand tool that does the job. It can selectively back up specific tables or even specific
rows. The output files are readable and can be modified manually if needed. However the bigger the database the less
viable this method becomes. Since it reads the whole data before dumping it, it affects internal work of the database,
making
other users experience higher latencies. The recovery process is also slow as the database must parse and apply all
those SQL statement one by one.

A hint: run logical backups during off-peak hours (e.g., late night).

### Physical Backup

Physical copying is usually much faster and less intrusive copy taking. It directly copies data files from
`/var/lib/mysql`
directory. The best tool for the job is `xtrabackup` developed by Percona. Note that the database frequently modifies
files while serving users, so file copying isn’t always safe and may lead to corruption. To prevent this the whole
database can be switched to a "backup mode" using `LOCK INSTANCE FOR BACKUP`. This mode will make MySQL to keep data
files
unmodified for the duration of the backup process.

During physical backup the tool copies actual files to a remote location. The copy process can take some time during
which the database still serves new requests. Since those transactions are not reflected in data files, they
accumulate in redo logs which are also copied. After the copy step is done, it needs to do one more step - apply redo
log to data files and bring them up to the date.

Physical copying usually much faster than logical, and mostly depends on the I/O capabilities of the disk system.
The restore phase is dead simple - the tool copies the files back to the instance's `/var/lib/mysql` and then the
instance can be run again.

### Binlog Backup For Almost-No-Data-Loss

Logical or Physical backups produce a snapshot of the database at a point in time. Even if we take them every hour,
still we can lose 1 hour worth of data in case of a sudden accident. If that is not acceptable, then what we can do is
constantly backup all changes that MySQL performs on the data.

Binlog is a log of all modifications (in data and tables structure) performed by the database. MySQL can work without
binlog enabled, but for us this feature is required (see `log_bin` config setting). The tool `mysqlbinlog` designed to
work with binlog files. It can stream binlog changes to another safe location. In case of a crash our
backup will contain all that it takes to restore all data with zero loss (almost):

1. First, restore the full backup—either physical or logical.
2. Apply all changes from the binlog dated later than the restored copy.

Operation is mostly manual, but straightforward. And depends on the skill of the database operator.

Important note: binlog streaming to a safe location does not guarantee no-data-loss as backup is happening after 
the fact of modification. It means that at the moment of the crash, some transactions could be still pending copying.
The only true solution for zero-data-loss is sync or semi-sync replication.

## Backups, Backups, and Conclusion.

Backups are protection from accidental or malicious data loss. No topology protects you from that. Replication schemas
protect against node crashes, network failure or disk problems, but not from human actions. Regular copies of the data
must be established. A good strategy is to make one full backup weekly and and perform incremental backups daily (or
hourly). Remember to verify copies via an automatic script to make sure they are not corrupted.

To achieve no-data-loss protection, you have to stream all changes from the primary's binlog at real time to a
safe location. `mysqlbinlog` is the way to do it.

By now, you spend around 60$ a month on your safe database setup, you have a backup process in place and ability to
rollback any failures within reasonable timeframe. You want to invest time and brainpower into studying how
mysqlshell, mysqlbinlog and other tools work together, design a few automation scripts for various occasions and setup
monitoring after the instances (which is another story, but a hint: look at PMM(Percona Monitoring and Management)).

## References

- [mysqldump](https://dev.mysql.com/doc/refman/8.4/en/mysqldump.html)
- [xtrabackup](https://docs.percona.com/percona-xtrabackup/8.4/)
- [LOCK INSTANCE FOR BACKUP](https://dev.mysql.com/doc/refman/8.4/en/lock-instance-for-backup.html)
- [mysqlbinlog backup](https://dev.mysql.com/doc/refman/8.4/en/mysqlbinlog-backup.html)
- [Replicaset](https://dev.mysql.com/doc/refman/8.4/en/mysql-innodb-replicaset-introduction.html)
- [Video: Kenny Gryp - Oracle - MySQL Architectures in a Nutshell - Percona Live 2021](https://www.youtube.com/watch?v=tsOOJWbq2Wo&t=262s)
- [Book: Introducing InnoDB Cluster](https://link.springer.com/book/10.1007/978-1-4842-3885-1)
- [mysqlshell manage replicaset](https://dev.mysql.com/doc/mysql-shell/8.0/en/mysql-innodb-replicaset.html)
- [Install semi-sync plugin](https://dev.mysql.com/doc/refman/8.4/en/replication-semisync-installation.html)
- [PMM by Percona](https://www.percona.com/software/database-tools/percona-monitoring-and-management)




