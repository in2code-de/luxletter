![LUX](Resources/Public/Icons/lux.svg "LUX")

# Luxletter - a newsletter system build in TYPO3

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

* Mail queue
* Configuration for sendervalues
* Link-rewriting for tracking
* Image include in newsletters?
* Dashboard
* HTML for newsletter optimization
* Opt-Out-Plugin
* Registration?
