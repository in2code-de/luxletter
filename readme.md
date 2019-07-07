![LUX](Resources/Public/Icons/lux.svg "LUX")

# Luxletter - a newsletter system build in TYPO3

Inspired by direct_mail

## Introduction

Email marketing tool in TYPO3. Just build and send newsletters to your customers.
This extension does not need EXT:lux but works together with the marketing automation tool for TYPO3.

## Screenshots

Example dashboard overview:\
\
![Example dashboard overview](Documentation/Images/documentation_dashboard.png "Dashboard")

Example newsletter list view:\
\
![Example dashboard overview](Documentation/Images/documentation_newsletterlist.png "Newsletter list")

## Technical requirements

* TYPO3 9 LTS is the basic CMS for this newsletter tool.
* EXT:lux is **not needed** to run luxletter but both extensions can work together to show more relevant information.
* This extension needs to be installed with composer (classic installation could work but is not supported).
* fe_users records are used to send emails to while fe_groups is used to select a group of them

## Installation with composer

```
composer require "in2code/luxletter"
```

## Changelog

| Version    | Date        | State      | Description                                                                        |
| ---------- | ----------- | ---------- | ---------------------------------------------------------------------------------- |
| 1.0.0      | coming soon | Task       | Initial release                                                                    |

## Todos

* done: Mail queue
* done: Configuration for sendervalues
* done: Link-rewriting for tracking via psr-15 (and disabling via data-attribute)
* done: Trackingpixel for openrate
* done: Unsubscribe-Plugin
* done: Dashboard
* done: New CType teaser to build newsletters with content elements
* in progress: HTML for newsletter optimization
* Image include in newsletters
* lux: identification with email clicks
* lux: new view in backend with all email receivers and there newletter actions
* lux: show email actions in normal detail view?
* Documentation (how to build newsletters, basic configuration, what can lux do, etc...)

### Todos for later

* Catch bounce mails
* HTML for news in newsletter
