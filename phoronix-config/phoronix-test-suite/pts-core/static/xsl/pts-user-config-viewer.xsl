<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Phoronix Test Suite - User Configuration File</title>
	</head>
	<body>
		<div style="width: 90%; margin: 20px auto 10px; text-align: left;">
			<p align="center"><img src="xsl/pts-logo.png" /></p>
			<p>The <em>user-config.xml</em> file contains the user configuration options for the Phoronix Test Suite. To edit any option, open <em>user-config.xml</em> within your preferred text editor. Alternatively, you can use the <em>user-config-set</em> option with the Phoronix Test Suite to update settings. For example, to set the download cache with the Phoronix Test Suite, execute <em>phoronix-test-suite user-config-set CacheDirectory=~/cache-directory/</em>. For additional information, view the documentation included with the Phoronix Test Suite or visit <a href="http://www.phoronix-test-suite.com/">Phoronix-Test-Suite.com</a>.</p>

			<h1>OpenBenchmarking Options</h1>
			<h3>AnonymousUsageReporting: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/OpenBenchmarking/AnonymousUsageReporting" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, anonymous usage information and statistics, like the tests that are run and their length of run, will be reported to <a href="http://www.openbenchmarking.org/">OpenBenchmarking.org</a> for analytical reasons. All submitted information is kept anonymous. For more information on the anonymous usage reporting, read the Phoronix Test Suite documentation.</p>
			<h3>IndexCacheTTL: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/OpenBenchmarking/IndexCacheTTL" /></span></h3>
			<p>The time to live for OpenBenchmarking.org index caches. This is an integer representing the number of days before an index cache should be automatically refreshed from OpenBenchmarking.org. The default value is <em>3</em> while setting the value to <em>0</em> will disable automatic refreshing of caches (caches can be manually updated at anytime using the respective command).</p>
			<h3>AlwaysUploadSystemLogs: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/OpenBenchmarking/AlwaysUploadSystemLogs" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, the system logs (i.e. dmesg, lspci, lsusb, Xorg.0.log) will always be uploaded to OpenBenchmarking.org when uploading your test results. Otherwise the user is prompted whether to attach the system logs with their results.</p>

			<h1>General Options</h1>
			<h3>DefaultBrowser: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/General/DefaultBrowser" /></span></h3>
			<p>The Phoronix Test Suite will automatically attempt to launch the system's default web browser when needed. This is done first by checking for x-www-browser and then xdg-open. If neither command is available, the Phoronix Test Suite will fallback to checking for Firefox, Epiphany, Mozilla, or the open command. If you wish to override the default browser that the Phoronix Test Suite selects, set this tag to the command name of the browser you wish to use. Leaving this tag empty will have the Phoronix Test Suite determine the default web browser.</p>
			<h3>UsePhodeviCache: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/General/UsePhodeviCache" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, the Phoronix Test Suite will use the Phodevi smart cache (if available). The Phodevi smart cache will automatically cache relevant system hardware/software attributes that can be safely stored and will be used until the system's software/hardware has changed or the system rebooted. Enabling this option will speed up the detection of installed hardware and software through the Phoronix Test Suite. If this option is set to <em>FALSE</em>, Phodevi will not generate a smart cache. The default value is <em>TRUE</em>.</p>
			<h3>DefaultDisplayMode: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/General/DefaultDisplayMode" /></span></h3>
			<p>This option affects how text is displayed on the command-line interface during the testing process. If this option is set to <em>DEFAULT</em>, the text interface will be the traditional Phoronix Test Suite output. If this option is set to <em>CONCISE</em>, the display mode is shorter and more concise. This is the default mode used during batch testing. The default value is <em>DEFAULT</em>.</p>
			<h3>PhoromaticServers: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/General/PhoromaticServers" /></span></h3>
			<p>This option can be used to specify the IP address(es) and port(s) of any Phoromatic Servers you wish to connect to for obtaining cached data, connecting to Phoromatic as a client test system, etc. The Phoronix Test Suite will attempt zero-conf network discovery but if that fails you can add the <em>IP:port</em> (the Phoromatic Server's HTTP port) to this element for targeted probing by the Phoronix Test Suite. Multiple Phoromatic Servers can be added if delimited by a comma; e.g. <em>IP:port,IP:port, IP:port</em>.</p>

			<h1>Modules Options</h1>
			<h3>LoadModules: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Modules/LoadModules" /></span></h3>
			<p>This tag contains a string of the names of the Phoronix Test Suite modules to load by default when running the Phoronix Test Suite. Multiple modules can be listed when delimited by a comma. Modules that load via setting an environment variable can also be specified here (i.e. <em>FORCE_AA=8</em> as an option in this string to load the <em>graphics_override</em> module with the 8x forced anti-aliasing). The default value is <em>toggle_screensaver, update_checker</em>.</p>

			<h1>Installation Options</h1>
			<h3>RemoveDownloadFiles: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Installation/RemoveDownloadFiles" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, once a test has been installed the downloaded files will be removed. Enabling this option will conserve disk space and in nearly all circumstances will not result in any problems. However, if a test profile directly depends upon a file that was downloaded (as opposed to something extracted from a downloaded file during the installation process), enabling this option will cause issues. If this option is set to <em>FALSE</em>, the downloaded files will not be removed unless the test is uninstalled. The default value is <em>FALSE</em>.</p>
			<h3>SearchMediaForCache: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Installation/SearchMediaForCache" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when installing a test it will automatically look for a Phoronix Test Suite download cache on removable media that is attached and mounted on the system. On the Linux operating system, the Phoronix Test Suite looks for devices mounted within the <em>/media/</em> or <em>/Volumes/</em> directories. If a download cache is found (a <em>download-cache/</em> folder within the drive's root directory) and a file it is looking for with matching MD5/SHA256 check-sum, the file will be automatically copied. Otherwise the standard download cache is checked. If this option is set to <em>FALSE</em>, removable media devices are not checked. The default value is <em>TRUE</em>.</p>
			<h3>SymLinkFilesFromCache: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Installation/SymLinkFilesFromCache" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, during the test installation process when a file is found in a Phoronix Test Suite download cache, instead of copying the file just provide a symbolic link to the file. Enabling this option will conserve disk space and in nearly all circumstances will not result in any issues, permitting the download cache files are always mounted during testing and are not located on removable media. If this option is set to <em>FALSE</em>, the files will be copied from the download cache. The default value is <em>FALSE</em>.</p>
			<h3>PromptForDownloadMirror: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Installation/PromptForDownloadMirror" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when downloading a test file the user will be prompted to select a mirror when multiple mirrors available. This option is targeted for those in remote regions or where their download speed may be greatly affected depending upon the server. If this option is set to <em>FALSE</em>, the Phoronix Test Suite will randomly pick a mirror. The default value is <em>FALSE</em>.</p>
			<h3>EnvironmentDirectory: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Installation/EnvironmentDirectory" /></span></h3>
			<p>This option sets the directory where tests will be installed to by the Phoronix Test Suite. The full path to the directory on the local file-system should be specified, though <em>~</em> is a valid character for denoting the user's home directory. The default value is <em>~/.phoronix-test-suite/installed-tests/</em>.</p>
			<h3>CacheDirectory: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Installation/CacheDirectory" /></span></h3>
			<p>This option sets the directory for the main download cache. The download cache is checked when installing a test while attempting to locate a needed test file. If the file is found in the download cache, it will not be downloaded from there instead of an Internet mirror. When running <em>phoronix-test-suite make-download-cache</em>, files are automatically copied to this directory. The full path to the directory should be specified, though <em>~</em> is a valid character for denoting the user's home directory. Specifying an HTTP or FTP URL is valid. The default value is <em>~/.phoronix-test-suite/download-cache/</em>. Multiple cache directories can be specified as of Phoronix Test Suite 2.2 with each directory being delimited by a colon.</p>

			<h1>Testing Options</h1>
			<h3>SleepTimeBetweenTests: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/SleepTimeBetweenTests" /></span></h3>
			<p>This option sets the time (in seconds) to sleep between running tests. The default value is <em>8</em>.</p>
			<h3>SaveSystemLogs: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/SaveSystemLogs" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when saving the results from a test it will also save various system details and logs to a sub-directory of the result file's location. Among the logs that will be archived include the X.Org log, dmesg, and lspci outputs. These system details may also be saved if a test suite explicitly requests this information be saved. If this option is set to <em>FALSE</em>, the system details / logs will not be saved by default. The default value is <em>FALSE</em>. When running in batch mode or using a Phoronix Certification and Qualification Suite, the logs will be saved regardless of this user setting.</p>
			<h3>SaveInstallationLogs: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/SaveInstallationLogs" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when saving the results from a test it will archive the complete output generated by the test during its earlier installation process. The log(s) are then saved to a sub-directory of the result file's location. If this option is set to <em>FALSE</em>, the full test logs will not be saved. The default value is <em>FALSE</em>. When running in batch mode or using a Phoronix Certification and Qualification Suite, the logs will be saved regardless of this user setting.</p>
			<h3>RemoveTestInstallOnCompletion: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/RemoveTestInstallOnCompletion" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, after a test has been completed, if that test profile is no longer present later in the test queue, the test installation will be removed from the disk. If the test is to be run at a later time, it will need to be re-installed. This is useful for embedded environments or Live CD/DVDs where the available memory (RAM) for storage may be limited.</p>
			<h3>SaveTestLogs: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/SaveTestLogs" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when saving the results from a test it will archive the complete output of each test's run generated by the application under test itself. The default value is <em>FALSE</em>.</p>
			<h3>ResultsDirectory: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/ResultsDirectory" /></span></h3>
			<p>This option sets the directory where test results will be saved by the Phoronix Test Suite. The full path to the directory on the local file-system should be specified, though <em>~</em> is a valid character for denoting the user's home directory. The default value is <em>~/.phoronix-test-suite/test-results/</em>.</p>
			<h3>AlwaysUploadResultsToOpenBenchmarking: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Testing/AlwaysUploadResultsToOpenBenchmarking" /></span></h3>
			<p>This option defines whether test results should always be uploaded to OpenBenchmarking.org upon their completion. If this value is set to <em>FALSE</em>, the user will be prompted each time whether the results should be uploaded to OpenBenchmarking.org, unless running in batch mode where the value is pre-defined. The default value is <em>FALSE</em>.</p>

			<h1>TestResultValidation Options</h1>
			<h3>DynamicRunCount: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/TestResultValidation/DynamicRunCount" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, the Phoronix Test Suite will automatically increase the number of times a test is to be run if the standard deviation of the test results exceeds a predefined threshold. This option is set to <em>TRUE</em> by default and is designed to ensure the statistical signifiance of the test results. The run count will increase until the standard deviation falls below the threshold or when the total number of run counts exceeds twice the amount that is set to run by default from the given test profile. Under certain conditions the run count may also increase further.</p>
			<h3>LimitDynamicToTestLength: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/TestResultValidation/LimitDynamicToTestLength" /></span></h3>
			<p>If <em>DynamicRunCount</em> is set to <em>TRUE</em>, this option sets a limit on the maximum length per trial run that a test can execute (in minutes) for the run count to be adjusted. This option is to prevent tests that take a very long amount of time to run from consuming too much time. By default this value is set to <em>20</em> minutes.</p>
			<h3>StandardDeviationThreshold: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/TestResultValidation/StandardDeviationThreshold" /></span></h3>
			<p>This option defines the overall standard deviation threshold (as a percent) for the Phoronix Test Suite to dynamically increase the run count of a test if this limit is exceeded. The default value is <em>3.50</em>.</p>
			<h3>ExportResultsTo: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/TestResultValidation/ExportResultsTo" /></span></h3>
			<p>This option can specify a file (either the absolute path or relative if contained within <em>~/.phoronix-test-suite/</em> where a set of test results will be passed as the first argument as a string with each of the test results being delimited by a colon. If the executed script returns an exit status of <em>0</em> the results are considered valid, if the script returns an exit status of <em>1</em> the Phoronix Test Suite will request the test be run again.</p>

			<h1>Batch Mode Options</h1>
			<p>The batch mode options are only used when using either the <em>batch-run</em> or <em>batch-benchmark</em> options with the Phoronix Test Suite. This mode is designed to fully automate the operation of the Phoronix Test Suite except for areas where the user would like to be prompted. To configure the batch mode options, it is recommended to run <em>phoronix-test-suite batch-setup</em> instead of modifying these values by hand.</p>
			<h3>SaveResults: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/BatchMode/SaveResults" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when running in batch mode the test results will be automatically saved.</p>
			<h3>OpenBrowser: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/BatchMode/OpenBrowser" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when running in batch mode the web-browser will automatically open when displaying test results. If this option is set to <em>FALSE</em>, the web-browser will not be opened.</p>
			<h3>UploadResults: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/BatchMode/UploadResults" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when running in batch mode the test results will be automatically uploaded to <a href="http://www.openbenchmarking.org/">OpenBenchmarking.org</a>.</p>
			<h3>PromptForTestIdentifier: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/BatchMode/PromptForTestIdentifier" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when running in batch mode the user will be prompted to enter a test identifier. If this option is set to <em>FALSE</em>, a test identifier will be automatically generated.</p>
			<h3>PromptForTestDescription: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/BatchMode/PromptForTestDescription" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when running in batch mode the user will be prompted to enter a test description. If this option is set to <em>FALSE</em>, the default test description will be used.</p>
			<h3>PromptSaveName: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/BatchMode/PromptSaveName" /></span></h3>
			<p>If this option is set to <em>TRUE</em>, when running in batch mode the user will be prompted to enter a test name. If this option is set to <em>FALSE</em>, a test name will be automatically generated.</p>

			<h1>Networking Options</h1>
			<h3>NoInternetCommunication: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Networking/NoInternetCommunication" /></span></h3>
			<p>If you wish to disable Internet communication within the Phoronix Test Suite by default, set this option to <em>TRUE</em>. The default value is <em>FALSE</em>. Setting this to <em>FALSE</em> will still allow Phoromatic to communicate with network servers such as for intranet-based download caches or a Phoromatic Server. Internet support is generally required for downloading test profiles from OpenBenchmarking.org, acquiring necessary test files from their respective sources, etc.</p>
			<h3>NoNetworkCommunication: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Networking/NoNetworkCommunication" /></span></h3>
			<p>If you wish to disable network support (including Internet access) entirely within the Phoronix Test Suite, set this option to <em>TRUE</em>. The default value is <em>FALSE</em>.</p>
			<h3>Timeout: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Networking/Timeout" /></span></h3>
			<p>This is the read timeout (in seconds) for network connections. The default value is <em>20</em>.</p>
			<h3>ProxyAddress: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Networking/ProxyAddress" /></span></h3>
			<p>If you wish to use a HTTP proxy server to allow the Phoronix Test Suite to communicate with OpenBenchmarking.org and other web services, enter the IP address / server name of the proxy server in this tag. If the proxy address and port tags are left empty but the <em>http_proxy</em> environment variable is set, the Phoronix Test Suite will attempt to use that as the proxy information.</p>
			<h3>ProxyPort: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Networking/ProxyPort" /></span></h3>
			<p>If using a proxy server, enter the TCP port in this tag.</p>

			<h1>Server Options</h1>
			<h3>RemoteAccessPort: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Server/RemoteAccessPort" /></span></h3>
			<p>If you wish to allow remote access to the built-in web-based interface to the Phoronix Test Suite when running its built-in web server, set the port number for remote access here. Port 80 is the common HTTP port but the Phoronix Test Suite web-interface can be easily set to other port numbers. If you do not wish to allow remote access, use the default value of <em>FALSE</em> or <em>-1</em>. If the value is set to <em>RANDOM</em>, a random port number will be chosen.</p>
			<h3>Password: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Server/Password" /></span></h3>
			<p>If you wish to require a password when entering the web-based interface to the Phoronix Test Suite -- either locally or remotely -- specify the password here using the password's SHA256 sum as the value.</p>
			<h3>WebSocketPort: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Server/WebSocketPort" /></span></h3>
			<p>The default port to use when running a WebSocket server. If no port is assigned or <em>RANDOM</em> is set, a random port will be chosen.</p>
			<h3>AdvertiseServiceZeroConf: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Server/AdvertiseServiceZeroConf" /></span></h3>
			<p>If this option is set to <em>TRUE</em> when starting a Phoromatic Server instance, the software will attempt to broadcast its service using zeroconf networking (Avahi on Linux assuming <em>avahi-publish</em> is present).</p>
			<h3>PhoromaticStorage: <span style="color: #CC0000;"><xsl:value-of select="PhoronixTestSuite/Options/Server/PhoromaticStorage" /></span></h3>
			<p>The location for the Phoromatic Server to store test results of connected systems, account information, etc. The default location is <em>~/.phoronix-test-suite/phoromatic/</em>.</p>
		</div>
		<div style="text-align: center; font-size: 12px;">Copyright &#xA9; 2008 - 2014 by <a href="http://www.phoronix-media.com/" style="text-decoration: none; color: #000;">Phoronix Media</a>.</div>
	</body>
</html>
</xsl:template>
</xsl:stylesheet>
