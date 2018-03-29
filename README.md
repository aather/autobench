![AutoBench](performance-meter.jpg)

## AutoBench Features

- Automate benchmarking when setup with CI/CD platform, like Netflix Spinnikar, AWS CodeDeploy
- Validate performance of OS. releases by running combination of cpu, memory, network, and storage standard benchmarks
- Run SPECjvm2008 cpu intensive benchmarks to stress test various aspects of jvm or java applications
- Identify regression trends by comparing benchmark runs across previous releases 
- Compare performance of AWS cloud instance types and families
- List AWS cloud instance hardware configuation and features for get better context on performance results
- All instances running benchmarks dump performance results in the same shared directory (NFS) for convenient processing. Web server is also given access to the same shared storage for automated reporting 
- Results are processed via cron job once a week. Webserver urls are updated when new results are available
- A single congiruation file (config.ini) for controlling type of results to process
- A single file that installs and execute all benchmarks
- Updated benchmark results are accessed via autobench home page: http://IP_Address/AMIbench/index.php

## AutoBench Design

## AutoBench Setup

## Autobench Benchark Setup

## Autobench Benchmark Reports

