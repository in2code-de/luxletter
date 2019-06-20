![LUX](Resources/Public/Icons/lux.svg "LUX")

# Luxletter - a newsletter system build in TYPO3

## Introduction

Just build and send newsletters to your customers.
This extension does not need EXT:lux but works together with the marketing automation tool for TYPO3.

## Technical requirements

* TYPO3 9 LTS is the basic CMS for this newsletter tool.
* EXT:lux is not needed to run luxletter but both extensions can work together
* This extension needs to be installed with composer (classic installation could work but is not supported).
* fe_users records are used to send emails to while fe_groups is used to select a group of them

## Installation with composer

```
composer require "in2code/luxletter"
```

## Changelog

| Version    | Date       | State      | Description                                                                        |
| ---------- | ---------- | ---------- | ---------------------------------------------------------------------------------- |
| 1.0.0      | ??         | Task       | Initial release                                                                    |

## Todos

* Mail queue
* Link-rewriting for tracking
* Image include in newsletters?
* Dashboard
* HTML for newsletter optimization
* Opt-Out-Plugin
* Registration?
