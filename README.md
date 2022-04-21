# Official Paymate Magento Extension

## What does it do?

This plugin allows you to accept Paymate payments through your website.

## Installation

<ol>
	<li> Download the Paymate plugin - Available as a .zip or tar.gz file from the GitHub directory. </li>
	<li> Unzip the file </li>
	<li> Create directory Marcwatts/Paymate in: <br/> <em>[MAGENTO_INSTAL_DIR]/app/code/</em></li>
	<li> Copy the extracted files to this folder folder </li>
	<li> Open Command Line Interface / Shell </li>
	<li> In CLI, run the below command to enable the module: <br/> <em>php bin/magento module:enable Marcwatts_Paymate</em> </li>
	<li> In CLI, run the Magento setup upgrade: <br/> <em>php bin/magento setup:upgrade</em> </li>
	<li> In CLI, run the Magento Dependencies Injection Compile: <br/> <em>php bin/magento setup:di:compile</em> </li>
	<li> In CLI, run the Magento Static Content deployment: <br/> <em>php bin/magento setup:static-content:deploy</em> </li>
	<li> Login to Magento Admin and navigate to System/Cache Management </li>
	<li> Flush the cache storage by selecting Flush Cache Storage </li>
</ol>

## Configuration

You will need to have a working merchant account and credentials to start, please contact https://global.paymate.com/

<ol>
	<li> Login to the Magento Admin and navigate to Stores/Configuration/Sales/Payment Methods</li>
	<li> Select Paymate and Enable</li>
	<li> Enter the Merchant Id and Password combination provided to you</li>
	<li> Specify an email address to receive debug emails from Paymate </li>
	<li> Save your settings</li>
</ol>