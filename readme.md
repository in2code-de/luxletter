![LUX](Resources/Public/Icons/lux.svg "LUX")

# Luxletter - Email marketing in TYPO3. Send newsletters the easy way.

Inspired by TYPO3 extension direct_mail - thank to the authors for the great work


## Introduction

Email marketing tool in TYPO3. Just build and send newsletters to your customers.
This extension does not need EXT:lux but works together with the marketing automation tool for TYPO3 to get even more
receiver analytics data.
Just add some HTML as content for your newsletters anywhere in the web or directly on your website, parse it and go for
it.


## Aspects of luxletter

### The upside

* A useful and nice **dashboard** shows you the relevant information in the backend
* Modern newsletter extension for TYPO3 9
* Tracking of clicks via **PSR-15 interface** in TYPO3
* Sending mails in queue via **symfony command controller**
* Records for **fe_groups** (and there related fe_users) are used to send mails to
* An **unsubscribe plugin** is already included
* A **teaser content element** helps you to create newsletters out of default content elements in TYPO3
* Every website can be used as prototype for your newsletter
* A **third party mailserver** can be used for newsletters

### The downside

* At the moment there is no bounce-management (Do you want to sponsor it? Contact us!)
* fe_users records are needed to send emails (sorry guys - no tt_address at the moment  )
* There is no registration plugin for fe_users in EXT:luxletter. Please use a different extension (like femanager) for this task


## Screenshots

Example dashboard overview:

![Example dashboard overview](Documentation/Images/documentation_dashboard.png "Dashboard")

Example newsletter im mail inbox:

![example mail](Documentation/Images/documentation_mail_newsletter.png "Example newsletter in mail inbox")

Example newsletter list view:

![Newsletter list](Documentation/Images/documentation_newsletterlist.png "Newsletter list")

Example newsletter creation - step 1:

![Step1](Documentation/Images/documentation_newnewsletter_step1.png "Creation: Step 1")

Example newsletter creation - step 2:

![Step2](Documentation/Images/documentation_newnewsletter_step2.png "Creation: Step 2")

Example newsletter creation - step 3:

![Step3](Documentation/Images/documentation_newnewsletter_step3.png "Creation: Step 3")

See the receiver activities if you have also installed the free extension lux:

![Receiver details](Documentation/Images/documentation_receiver_detail.png "Receiver details with free extension lux")

Create teasers from content elements with a teaser plugin:

![Teaser content elements](Documentation/Images/documentation_content_teaser.png "Create teasers")


## Documentation

See the full [documentation](Documentation/Index.md) (installation, configuration, newsletters and analysis)


## Technical requirements

* TYPO3 9 LTS is the basic CMS for this newsletter tool.
* EXT:lux is **not needed** to run luxletter but both extensions can work together to show more relevant information.
* This extension needs to be **installed with composer** (classic installation could work but is not supported and tested).
* fe_users records are used to send emails to while fe_groups is used to select a group of them


## Sponsored features (please get in contact to us if you want to sponsor a new feature)

* Import of fe_users from tt_address (to migrate easier from direct_mail to luxletter)
* Editview of existing newsletters (reparse function?)
* Move global newsletter configuration to records to use different settings per newsletter


## Installation with composer

```
composer require "in2code/luxletter"
```

## Changelog

| Version    | Date        | State      | Description                                                                                          |
| ---------- | ----------- | ---------- | ---------------------------------------------------------------------------------------------------- |
| 1.2.1      | 2019.11.26  | Bugfix     | Fix problem on packagist.org                                                                         |
| 1.2.0      | 2019.11.26  | Task       | Show helpful messages in some exceptional cases. Use mediumtext for bodytext for more space now.     |
| 1.1.1      | 2019.09.19  | Bugfix     | Don't throw an exception for empty fe_users.crdate fields                                            |
| 1.1.0      | 2019.08.20  | Bugfix     | Some css fixes, Fix for default image and small code cleanup                                         |
| 1.0.0      | 2019.08.02  | Task       | First stable TER release with a useful documentation                                                 |
| 0.3.0      | 2019.07.31  | Task       | Support for lux, Add signal, Receiver module                                                         |
| 0.2.0      | 2019.07.13  | Task       | Fix for PHP 7.3, Fix for default sql mode setting, documentation update                              |
| 0.1.0      | 2019.07.10  | Task       | Initial release of a working newsletter extension                                                    |



## Need help with email-marketing or marketing automation?

The company behind Lux, LuxLetter and LuxEnterprise - the complete marketing box for TYPO3 - is looking 
forward to help you: https://www.in2code.de
