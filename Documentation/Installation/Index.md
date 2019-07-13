<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

# Luxletter - Email marketing in TYPO3. Send newsletters the easy way.

## Installation

Extension luxletter should be installed via composer

```
composer require "in2code/luxletter"
```

Note: Installation without composer could work but is not supported. 
TYPO3 9.5 is required. Extension lux can be also installed for more analysis but is not 
neccessary.

### Basic settings in extension configuration

coming soon...


### TypoScript

Static template `Basic TypoScript` must be included\
In addition `FluidStyledMailContent` static template can also be added.

coming soon...


### Add page for a unsubscribe plugin

coming soon...


### Define fe_groups records for receiver groups

coming soon...


### Connect a mailserver

If you don't set a mail configuration for luxletter, 
the default mail configuration from TYPO3 will be used.
But it's highly recommended to set a different mailserver for your newsletter configuration.
Such setting can be done in your typo3conf/AdditionalConfiguration.php file like:

```
$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport'] = 'smtp';
$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_server'] = 'sslout.de:465';
$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_encrypt'] = 'ssl';
$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_username'] = 'username';
$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_password'] = 'password';
$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_port'] = '465';
```


### Configure the queue task

coming soon...
