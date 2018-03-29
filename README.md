![AutoBench](performance-meter.jpg)

## AutoBench Features

- Automate benchmarking when setup with CI/CD platform, like Netflix Spinnikar, AWS CodeDeploy.
- Identify regression trends by comparing benchmark runs across previous releases. 
- Compare performance of AWS cloud instance types and families.
- Validate performance of OS. releases by running combination of cpu, memory, network, and storage standard benchmarks
- Run SPECjvm2008 cpu intensive benchmarks to stress test various aspects of jvm or java applications
- List AWS cloud instance hardware configuation and instance features with benchmark results
- All instances dump benchmark results in the shared directory (NFS) for easy processing. Web server is also given access to the same shared directory   
- Results are processed via cron job once a week with links updated to new results.
- A single congiruation file (config.ini) for controlling type of benchmark results to process
- A single file that setup and execute all benchmarks
- Updated benchmark results are accessed via autobench home page: http://IP_Address/AMIbench/index.php

## AutoBench Design


## AutoBench Setup

## Autobench Benchark Suite

## Autobench Reporting

