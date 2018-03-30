![AutoBench](performance-meter.jpg)

## AutoBench Features

- Automates benchmark runs when setup via CI/CD platform, like Netflix Spinnaker, AWS CodeDeploy.
- Identifies regression trends by comparing benchmark results with previous runs. 
- Helps compare performance of AWS cloud instances within and across instance families.
- Validates performance of OS. releases by running industry standard open source system benchmarks
- Runs SPECjvm2008 cpu intensive benchmarks to stress test various aspects of jvm or java applications
- Lists AWS cloud instance hardware configuation and instance features with benchmark results
- Benchmark results are accumulated in a shared directory (NFS) for ease of processing
- Results are aggregated and merged to allow comparison to be performed in a variety of ways.
- A single file to setup and execute all benchmarks
- A single configuration file (config.ini) for describing type of benchmark results to process
- Web interface. Home page: **http://ipaddress/AMIbench/index.php**
- Autobench is tested on Ubuntu xenial.
![Autobench](homepage.png)

## AutoBench Design
AutoBench is built using open source phoronix test suite http://phoronix-test-suite.com benchmarking framework. There are ready to use benchmarks, called **test profiles**, available for running standard benchmarks like: openssl, 7zip-compress, Stream etc.. Framework is extensible that allows easy integration of custom benchmarks. Test profiles are stored in directory:**/var/lib/phoronix-test-suite/test-profiles**. Each test profile is configured using four files listed below:
- **downloads.xml (optional):**  instruction on downloading benchmark source code or binaries 
- **install.sh:** instructions on compiling, if required,  and installing benchmark 
- **test-definition.xml:** Type of information provided in this file: Number of iterations, build dependencies, test descritpion, default options, measurement unit, supported OS, version etc..
- **results-definition.xml:** Benchmark output is filtered via unique pattern that fetches key metrics

Type of benchmarks available:

- **cpu benchmarks:** encode-mp3, ffmpeg, openssl, compress-7zip, sysbench-cpu, lmbench-mhz, kernel-build,
- **memory benchmarks:** cachebench, stream, stream-singlecpu,sysbench-mem,lmbench-mem, lmbench-bw
- **SPECjvm2008 benchmarks:** scimark-fft-large, cryto-aes, derby, compress, mpegaudio ...

![Autobench](cpu-mem-benchmarks.png)

Phoronix test suite is also bundled with sensors or monitors for capturing useful metrics during benchmark run:
- **cpu monitor:** cpu usage
- **memory monitor:** memory usage
- **storage monitor:** storage throughput 
- **Linux perf and Flame Graph  monitors:** Linux perf metrics and flamegraph for highlighting hot cpu functions
- **performance/cost monitor:** Calculate performance per dollar. You can replace dollar with other units like: cpu, memory etc..

**Documentation: https://www.phoronix-test-suite.com/documentation/phoronix-test-suite.html**

All benchmarks are dumped into a shared NFS directory (prefered). Results are stored in seperate directories. For example:
- **cputests-openssl-190-i2-xlarge-LATEST:** Latest iteration of openssl cpu benchmark ran on AWS i2.xlarge instance 
- **cputests-openssl-190-i2-xlarge-440-96-generic-201803291856:** All previous iterations of the same test ran on i2.xlarge instance with date stamp 

memory (memtests-stream..) and java (javatests-java-cryto..) benchmarks also use similar directory names. Results are stored in **json, txt, css and svg** format.

## AutoBench Setup

- $ git clone https://github.com/aather/autobench.git
- $ cd autobench 
- $ ./setup.sh 

**setup.sh** Script installs all required packages and configure autobench environment. There is not much error checking performed in setup.sh script. Purpose is to quickly setup a demo environment to play with it. I suggest reviewing **setup.sh" script and, if possible, install autobench in a virtualbox VM to avoid accidently overwriting system configuration. Script **setup.sh** will also install sample test reports that can be viewed via web brower by visiting:

 **http://ipaddress/AMIbench/index.php**

**Caution:** Update autobench environment file **/etc/autobench_environment.sh** if not running on a AWS cloud instance

## Autobench Benchmark Suite
You can run all benchmarks by executing **/usr/share/phoronix.runtest** or run individually. Make sure to edit autobench environment file **/etc/autobench_environment.sh** if not running on a AWS cloud instance. 

Example: To run a single benchmark, **compress-7zip**, do the following: 

- **$sudo /usr/bin/phoronix-test-suite-cputests install Test pts/compress-7zip-1.6.2**
- **$sudo /usr/bin/phoronix-test-suite-cputests batch-run Test pts/compress-7zip-1.6.2**

As mentioned earlier, all benchmarks are stored in **/var/lib/phoronix-test-suite/test-profiles**. When you run  above commands, tests will be installed in **/usr/share/test-suites** directory as a script and executed. Results are dumped in **/efs/autobench/test-results** (default) directory.

## Autobench Reporting
Autobench reporting is managed by **config.ini** file. All autobench scripts include **config.ini** file to customize web pages and to choose what benchmark results to aggregate and merge for comparison purposes. config file and php scripts are installed in directory: **/var/www/html/AMIbench**

**cacheresults.php** can be run manually or via cron to process benchmark results specified in the **config.ini** file. Results are merged using phoronix utility. Graphs are generated via phpgraph library.

- Compare AWS instances performance within the same family (m5,c4,d2,i3..)
[!Autobench](instfamily.png)
- Compare AWS instance types performance across AWS families(xlarge,2xlarge,4xlarge..)
[!Autobench](instfamily.png)
- Find regression trends by comparing benchmark results from multiple runs on the same instance (m4.2xl,c4.8xl..)
[!Autobench](instregression.png)
