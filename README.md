![AutoBench](performance-meter.jpg)

## AutoBench Features

- Automate benchmarking when setup with CI/CD platform, like Netflix Spinnikar, AWS CodeDeploy.
- Identify regression trends by comparing benchmark runs across previous releases. 
- Compare performance of AWS cloud instance types and families.
- Validate performance of OS. releases by running industry standard open source system benchmarks
- Run SPECjvm2008 cpu intensive benchmarks to stress test various aspects of jvm or java applications
- List AWS cloud instance hardware configuation and instance features with benchmark results
- All instances dump benchmark results in the shared directory (NFS) for easy processing. Web server is also given access to the same shared directory   
- Results are processed via cron job once a week with links updated to new results.
- A single congiruation file (config.ini) for controlling type of benchmark results to process
- A single file that setup and execute all benchmarks
- Updated benchmark results are accessed via autobench home page: http://IP_Address/AMIbench/index.php

## AutoBench Design
AutoBench is built using open source phoronix test suite http://phoronix-test-suite.com benchmarking framework. There are ready to use benchmarks, called **test profiles**, available for running standard benchmarks like: openssl, 7zipcompression, stream etc.. Framework is extensible that allows easy integration of custom benchmarks. Test profiles are installed in directory:**/var/lib/phoronix-test-suite/test-profiles**. Each test profile is consist of four files:
- **downloads.xml (optional):**  instruction on downloading benchmark source code or binaries 
- **install.sh:**  install instructions. This converts it into shell to run your test with options
- **test-definition.xml:** information about number of iterations, build dependencies,default options, measurement Unit, supported OS, version etc.. is provided
- **results-definition.xml:** Benchmark output is filtered via unique pattern that fetches key metrics

## AutoBench Setup

## Autobench Benchark Suite

## Autobench Reporting

