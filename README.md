![AutoBench](performance-meter.jpg)

## AutoBench Features

- Automate benchmarking when setup with CI/CD platform, like Netflix Spinnikar, AWS CodeDeploy.
- Identify regression trends by comparing benchmark runs across previous releases. 
- Compare performance of AWS cloud instance types and families.
- Validate performance of OS. releases by running industry standard open source system benchmarks
- Run SPECjvm2008 cpu intensive benchmarks to stress test various aspects of jvm or java applications
- List AWS cloud instance hardware configuation and instance features with benchmark results
- All instances dump benchmark results in shared directory (NFS) for ease of processing. Web server is also given access to the same shared directory   
- Results are processed via cron job once a week with links updated to new results.
- A single configuration file (config.ini) for controlling types of results to process
- A single file that setup and execute all benchmarks
- Updated benchmark results are accessed via autobench home page: http://IP_Address/AMIbench/index.php

## AutoBench Design
AutoBench is built using open source phoronix test suite http://phoronix-test-suite.com benchmarking framework. There are ready to use benchmarks, called **test profiles**, available for running standard benchmarks like: openssl, 7zip-compress, Stream etc.. Framework is extensible that allows easy integration of custom benchmarks. Test profiles are stored in directory:**/var/lib/phoronix-test-suite/test-profiles**. Each test profile is configured using four files listed below:
- **downloads.xml (optional):**  instruction on downloading benchmark source code or binaries 
- **install.sh:** instructions on compiling, if required,  and installing benchmark 
- **test-definition.xml:** information about number of iterations, build dependencies, default options, measurement unit, supported OS, version etc.. is provided
- **results-definition.xml:** Benchmark output is filtered via unique pattern that fetches key metrics

Phoronix test suite is bundled with sensors or monitors for capturing useful metrics during benchmark run:
- **cpu monitor:** cpu usage
- **memory monitor:** memory usage
- **storage monitor:** storage throughput 
- **Linux perf and Flame Graph  monitors:**Linux perf metrics and flamegraph for highlighting hot cpu functions
- **performance/cost monitor:** Calculate performance per dollar. You can replace dollar with other units like: cpu, memory etc..

**Documentation: https://www.phoronix-test-suite.com/documentation/phoronix-test-suite.html**

All benchmarks are dumped into a shared NFS directory (prefered). Results are stored in seperate directories wistring like:
- **cputests-openssl-190-i2-xlarge-LATEST:** Latest iteration of openssl cpu benchmark ran on AWS i2.xlarge instance
- **cputests-openssl-190-m3-medium-440-96-generic-201803291856:** All previous iterations of the same test with date stamp 

memory (memtests-stream..) and java (javatests-java-cryto..) benchmarks also use similar directory names. Each test directory contains benchmark results in format: **json, txt, css and svg**

## AutoBench Setup

- set
## Autobench Benchark Suite

## Autobench Reporting

