![AutoBench](performance-meter.jpg)

## AutoBench Features

- Automates benchmarking when setup via CI/CD platform, like Netflix Spinnaker, AWS CodeDeploy.
- Identifies regression trends by comparing benchmark results with previous runs. 
- Helps compare performance of AWS cloud instances within and across instance families.
- Validates performance of OS. releases by running industry standard open source system benchmarks
- Runs SPECjvm2008 cpu intensive benchmarks to stress test various aspects of jvm or java applications
- Lists AWS cloud instance hardware configuation and instance features with benchmark results
- Benchmark results are accumulated in a shared directory (NFS) for ease of processing
- Results are aggregated and merged to allow comparison to be performed in a variety of ways.
- A single file to setup and execute all benchmarks
- A single configuration file (config.ini) for selecting type of benchmark results to process
- Web interface. Home page: **http://ipaddress/AMIbench/index.php**
- Autobench is tested on Ubuntu xenial.
![Autobench](homepage.png)

## AutoBench Design
AutoBench is built using open source phoronix test suite http://phoronix-test-suite.com benchmarking framework. There are ready to use standard benchmarks available like: openssl, 7zip-compress, Stream etc.. Framework is extensible and allows easy integration of custom benchmarks. Each benchmark is defined as a test profile, stored in directory:**/var/lib/phoronix-test-suite/test-profiles**, and have following four files:
- **downloads.xml (optional):**  Download instructions for downloading source code or required packages 
- **install.sh:** Instructions on compiling, if needed, and configuring benchmarks.  
- **test-definition.xml:** benchmarks attributes such as: iterations, build dependencies, descritpion, measurement unit, supported OS, version, etc..
- **results-definition.xml:** Benchmark output is filtered to fetch a key metric use for reporting results.

Type of benchmarks available:

- **cpu benchmarks:** encode-mp3, ffmpeg, openssl, compress-7zip, sysbench-cpu, lmbench-mhz, kernel-build,
- **memory benchmarks:** cachebench, stream, stream-singlecpu, sysbench-mem, lmbench-mem, lmbench-bw
- **Storage benchmarks:** 
- **SPECjvm2008 benchmarks:** scimark-fft-large, cryto-aes, derby, compress, mpegaudio ...

![Autobench](cpu-mem-benchmarks.png)

System metrics (cpu, memory, io, net) are collected as wells as profiling data using linux perf. System usage are compared across cloud instances. Flamegraph and IPC/CPI (Instruction per cycle or cycles per instrunction) ar also reported with results. 

All benchmarks are dumped into a shared NFS directory (prefered). Results are stored in seperate directories, for example:
- **cputests-openssl-190-i2-xlarge-LATEST:** Latest iteration of openssl cpu benchmark ran on AWS i2.xlarge instance 
- **cputests-openssl-190-i2-xlarge-440-96-generic-201803291856:** All previous iterations of the same test ran on i2.xlarge instance with date stamp 

memory (memtests-stream..) and java (javatests-java-cryto..) benchmarks also use similar directory names. Results are stored in **json, txt, css and svg** format.

## AutoBench Setup

- $ git clone https://github.com/aather/autobench.git
- $ cd autobench 
- $ ./setup.sh 

**setup.sh** script installs all required packages and configures autobench environment. There is not much error checking performed in setup.sh script. Purpose is to quickly setup a demo environment to play with it. I suggest reviewing **setup.sh** script and, if possible, installing autobench in a virtualbox VM for quick testing. Script **setup.sh** will also install sample test reports that can be viewed via web brower by visiting:

 **http://ipaddress/AMIbench/index.php**

**Caution:** Update autobench environment file **/etc/autobench_environment.sh** if not running on a AWS cloud instance

For proper testing, I propose:
- Set up few AWS instance types (medium,large,xlarge..) from different families (m4,i3,t2..) 
- Execute all benchmarks by running: /usr/share/phoronix-runtests
- Run benchmarks multiple times on each instance in order to have few sets available to perform useful comparison 
- Ideally, all instances should dump benchmark results into NFS mounted shared directory. Otherwise, copy all results into a common directory on a webserver. Default directory is: /efs/autobench/test-results
- To process results, edit '**config.ini**' file in directory $WEBDIR/AMIbench. 
- Comment out (;) lines that do not apply.  For example:
  - Comment out instance types that are not tested. If you don't have more than one instance of the same type (xl) from different families then comment out all entries under "**instTypes to compare**" section since there is nothing to compare 
  - Comment out instance families that are not tested. If you don't have more than one instance from each family, then comment out all entries under "**instFamily to compare**" section since there is nothing to compare.
  - Comment out instances that are not tested under the section "**InstRegression**" 
  - There are two lines per benchmark test. Comment out benchmark tests that you did not run
  - Once "config.ini" file is setup, process benchmark results using "**cacheresults.php**" script located in dir: **$WEBDIR/AMIbench**
  - You can now access updated results by visiting autobench home page: http://ip-address/AMIbench/index.php

## Autobench Benchmark Suite
You can run all benchmarks by executing **/usr/share/phoronix.runtest** or run individually. Make sure to edit autobench environment file **/etc/autobench_environment.sh** if not running on a AWS cloud instance. 

Example: To run a single benchmark, for example: **compress-7zip**, do the following: 

- **$sudo /usr/bin/phoronix-test-suite-cputests install Test pts/compress-7zip-1.6.2**
- **$sudo /usr/bin/phoronix-test-suite-cputests batch-run Test pts/compress-7zip-1.6.2**

As mentioned earlier, all benchmarks or test profiles are stored in **/var/lib/phoronix-test-suite/test-profiles**. When you run  above commands, tests will be installed in **/usr/share/test-suites** directory as a script and executed. Results are dumped in **/efs/autobench/test-results** (default) directory.

## Autobench Reporting
Autobench reporting is managed by **config.ini** file. All autobench scripts include **config.ini** file to customize web pages and to choose what benchmark results to aggregate and merge for comparison purposes. config file and php scripts are installed in directory: **/var/www/html/AMIbench**

There are multiple scripts provided that process benchmark results:
**regress_process_graphs.php**: aggregate all iterations of the same benchmarks with assoicated system metrics and generate graphs. 
**prod_cacheresults.php**: graph and compare benchmarks results executed across various cloud instances
**type_process_graphs.php**: graph and compare system usage of the benchmarks executed across various cloud instance **types**
**family_procoess_graphs.php**: graph and compare system usage of benchmarks executed across various cloud instance **families**

- Compare AWS instances performance within the same family (m5,c4,d2,i3..)
![Autobench](instfamily.png)
- Compare AWS instance types performance across AWS families(xlarge,2xlarge,4xlarge..)
![Autobench](instfamily.png)
- Find regression trends by comparing benchmark results from multiple runs on the same instance (m4.2xl,c4.8xl..)
![Autobench](instregression.png)
