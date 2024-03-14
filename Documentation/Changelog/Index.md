<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

# Luxletter - Email marketing in TYPO3. Send newsletters the easy way.


## Changelog

**Note:** !!! Scroll down for breaking changes instructions

| Version    | Date       | State   | Description                                                                                                                                                                                      |
|------------|------------|---------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 25.0.4     | 2024.03.07 | Bugfix  | Prevent undefined array key exception if luxletterUnsubscribePid was not set                                                                                                                     |
| 25.0.3     | 2024.03.04 | Bugfix  | Add "luxletterlink" to excluded chash configuration                                                                                                                                              |
| 25.0.2     | 2024.03.01 | Bugfix  | Show fields of configuration table always for editors                                                                                                                                            |
| 25.0.1     | 2024.02.07 | Bugfix  | Prevent exception on tracked links if a user is deleted/disabled                                                                                                                                 |
| 25.0.0     | 2024.02.02 | Feature | Add two new events for newsletter manipulation, update LUXletter for news 11, bugfix for unsubscribe action in TYPO3 12                                                                          |
| 24.0.2     | 2023.10.05 | Bugfix  | Respect `asynchronousQueueStorage` configuration even if newsletters are generated from CLI now                                                                                                  |
| 24.0.1     | 2023.09.29 | Bugfix  | Revive `$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']` configuration for TYPO3 11 and 12                                                                                                         |
| 24.0.0     | 2023.09.05 | Feature | Add another unsubscribe plugin where the receiver can select his preferences                                                                                                                     |
| 23.0.1     | 2023.08.19 | Bugfix  | Fix backend module configuration for TYPO3 12 (to set correct editor permissions)                                                                                                                |
| 23.0.0     | 2023.08.16 | Feature | Change main color for a better readability of LUXletter, Allow to build queue from CLI if for big newsletters, Allow to change user via event in UserFactory                                     |
| 22.1.1     | 2023.07.16 | Bugfix  | Show name of receiver groups again in list view (broken since multilanguage support), fix a small typo for possible wrong orderings error, fix by adding receivers also to localized newsletters |
| 22.1.0     | 2023.07.16 | Feature | Show the number of already sent testmail for a better usability. Log email failures now. Bugfix of the wrong failure calculation.                                                                |
| 22.0.0     | 2023.06.15 | Feature | Add a basic queue failure management for undispatchable mails (try to send 3 times from now on). Add a description text for LUXletter category in backend.                                       |
| 21.0.0     | 2023.05.15 | Feature | Add local docker environment, update unit tests for newer PHP and TYPO3, fix problems with activated cachehash checks, some optical improvements in backend views, updated frontend toolchain    |
| 20.0.0     | 2023.04.25 | Feature | Add unsubscribe URL in header for unsubscribe automatism in email clients (new configuration option in extension manager available)                                                              |
| 19.2.2     | 2023.04.14 | Bugfix  | Prevent exception in backend modules in TYPO3 12.3 or newer related to getTSFE() condition                                                                                                       |
| 19.2.1     | 2023.04.03 | Bugfix  | Add setter to manipulate content in NewsletterUrlAfterParsingEvent                                                                                                                               |
| 19.2.0     | 2023.03.23 | Feature | Doktype number is configurable for multilanguage mode, support to split tables via tablemapping if needed (e.g. with content publisher)                                                          |
| 19.1.0     | 2023.03.06 | Task    | Some smaller CSS changes for TYPO3 12, Update for chart.js to version 2.9.4 for security reasons                                                                                                 |
| 19.0.1     | 2023.02.07 | Bugfix  | Fix empty eval string in TCA configuration of sites.luxletterUnsubscribePid in TYPO3 12                                                                                                          |
| 19.0.0     | 2022.12.19 | Feature | Replace checkboxes with choices plugin for receiver selection for a better handling especially with a lot of receiver groups                                                                     |
| !!! 18.0.0 | 2022.12.13 | Task    | TYPO3 12 and 11 supported now, dropped support for TYPO3 10                                                                                                                                      |
| 17.7.0     | 2023.08.17 | Feature | Allow to build queue from CLI if for big newsletters; Change main color for a better readability of LUXletter                                                                                    |
| 17.6.3     | 2023.07.21 | Bugfix  | Prevent exception in newsletter preview (e.g. when RouteEnhancers are in use), Extend DummyUserEvent with a setter for manipulation possibilities                                                |
| 17.6.2     | 2023.07.16 | Bugfix  | Fix calculation of failure percentage, log failures now                                                                                                                                          |
| 17.6.1     | 2023.06.20 | Bugfix  | Fix of an annotation line                                                                                                                                                                        |
| 17.6.0     | 2023.06.19 | Feature | Add a basic queue failure management for undispatchable mails (try to send 3 times from now on).                                                                                                 |
| 17.5.1     | 2023.04.03 | Bugfix  | Add setter to manipulate content in NewsletterUrlAfterParsingEvent                                                                                                                               |
| 17.5.0     | 2023.03.23 | Feature | Doktype number is configurable for multilanguage mode, support to split tables via tablemapping if needed (e.g. with content publisher)                                                          |
| 17.4.0     | 2022.12.19 | Feature | Replace checkboxes with choices plugin for receiver selection for a better handling especially with a lot of receiver groups                                                                     |
| 17.3.0     | 2022.11.25 | Task    | Remove config.no_cache=1, small label change, fix typo in documentation                                                                                                                          |
| 17.2.0     | 2022.10.18 | Feature | Track open when a link is clicked (and there was no open log entry yet), Always show open rate by the already dispatched newsletters, enable multiLanguageMode per default                       |
| 17.1.0     | 2022.10.14 | Bugfix  | Enable sectionmenu switch in teaser element, fix users preview together with sql_mode=only_full_group_by                                                                                         |
| 17.0.2     | 2022.10.05 | Bugfix  | Remove unwanted Fluid namespace dependencies to EXT:lux if LUX is not installed at the same time                                                                                                 |
| 17.0.1     | 2022.10.04 | Bugfix  | Remove unwanted PHP dependencies to EXT:lux to prevent Category exception if LUX is not installed at the same time                                                                               |
| !!! 17.0.0 | 2022.10.03 | Feature | Multiple receiver groups can now be selected for sending newsletters (of course if an email is shared in more usergroups, the newsletter will only be sent once per mail)                        |
| 16.0.0     | 2022.09.29 | Feature | Newsletters can now be edited later from the list view.                                                                                                                                          |
| 15.0.0     | 2022.09.28 | Feature | Newsletters can be categorized now. Grouped list view by category. Added a filter above the list view.                                                                                           |
| 14.1.0     | 2022.08.30 | Feature | Image embedding: Don't attach the same image twice or even more often                                                                                                                            |
| 14.0.1     | 2022.08.19 | Bugfix  | Fix "newsletter is not ready yet" bug in multilanguage mode                                                                                                                                      |
| 14.0.0     | 2022.07.06 | Task    | Fix the overview charts in the dashboards, always show newletter names, increase height of some charts                                                                                           |
| 13.0.2     | 2022.05.24 | Bugfix  | Fix regression with 13.0.1 and also added EXT:dashboard to suggest parts in composer.json and ext_emconf.php                                                                                     |
| 13.0.1     | 2022.05.23 | Bugfix  | Prevent exception "Call to a member function isPackageActive() on null" in TYPO3 11                                                                                                              |
| !!! 13.0.0 | 2022.05.22 | Feature | Reduce technical debt by replacing outdated signalslots with PSR-14 eventdispatchers                                                                                                             |
| 12.0.0     | 2022.04.07 | Task    | One unsubscribe page can be used for multiple newsletters with different receiver groups (we simply removed the usergroup selection in FlexForm)                                                 |
| 11.1.0     | 2022.04.04 | Task    | Add limit for receiver list, add limits for activities and logs, harden template paths, removed unneeded CSS                                                                                     |
| 11.0.2     | 2022.04.04 | Bugfix  | Clean internal SVG files from id and class attributes                                                                                                                                            |
| 11.0.1     | 2022.04.02 | Bugfix  | Fix wrong sql structure definition                                                                                                                                                               |
| !!! 11.0.0 | 2022.03.11 | Feature | Multilanguage mode added for luxletter, Improve exception message for invalid unsubscribe pid, Limit mail sending to given TYPO3 context                                                         |
| 10.2.1     | 2022.01.04 | Bugfix  | Fix newsletter statistics with a comma in the name                                                                                                                                               |
| 10.2.0     | 2021.12.08 | Bugfix  | Allow embedding of more then only 9 images, add automatic tests for Execution class                                                                                                              |
| 10.1.0     | 2021.11.23 | Feature | Add mysql table indices for a better performance, show dummy images in news list if there are now news, allow multiple embedding of the the same image now                                       |
| 10.0.0     | 2021.11.19 | Feature | Enable automatic embedding of images into newsletter mails                                                                                                                                       |
| 9.0.4      | 2021.11.09 | Bugfix  | doctrine/dbal >= 2.11.0 is supported since luxletter supports TYPO3 11 - updated requirements in composer.json file                                                                              |
| 9.0.3      | 2021.11.09 | Bugfix  | Use absolute URL for news list view                                                                                                                                                              |
| 9.0.2      | 2021.11.03 | Bugfix  | Fix command return values for TYPO3 10                                                                                                                                                           |
| 9.0.1      | 2021.11.03 | Bugfix  | Mainly a documentation update, also added a _blank to "go enterprise" link                                                                                                                       |
| 9.0.0      | 2021.11.01 | Feature | Create newsletters from CLI or scheduler task now (if needed to automaticly create frequently newsletters - e.g. every week)                                                                     |
| !!! 8.0.0  | 2021.10.29 | Feature | Different newsletter layouts can now be selected from the editor                                                                                                                                 |
| 7.1.0      | 2021.10.27 | Task    | Fix date converting problem when adding newsletters with a date, add confirm message when deleting newsletters, update source description                                                        |
| 7.0.0      | 2021.10.27 | Feature | Add a fluidStyleMailContent template for EXT:news listview, hide unwanted tables in backend list view, fix label                                                                                 |
| 6.0.1      | 2021.10.21 | Bugfix  | Reanimate unsubscribe link, year and tracking pixel                                                                                                                                              |
| 6.0.0      | 2021.10.17 | Task    | Support TYPO3 11 (and also 10)                                                                                                                                                                   |
| 5.0.1      | 2021.10.10 | Bugfix  | Don't include removed ZerbCss.html any more in version 5                                                                                                                                         |
| !!! 5.0.0  | 2021.10.06 | Feature | Adding css inline in html-tags now (see TypoScript setup how to include your css files)                                                                                                          |
| 4.3.0      | 2021.10.01 | Feature | Improve signals in ParseNewsletterUriService class, small cleanup                                                                                                                                |
| 4.2.0      | 2021.08.30 | Feature | Add some more signals, make ParseNewsletterService more extendable, use better method to get base from site configuration, add .gitattributes file                                               |
| 4.1.1      | 2021.08.12 | Bugfix  | Don's throw an error if dashboard is not available                                                                                                                                               |
| 4.1.0      | 2021.07.27 | Feature | Add automatic tests with github actions                                                                                                                                                          |
| !!! 4.0.0  | 2021.06.10 | Feature | Multiple sender configuration supported (see breaking changes above), Testmails can be send multiple times, TYPO3 9 support finally dropped                                                      |
| 3.1.4      | 2021.06.04 | Bugfix  | Allow rendering of widgets without EXT:lux                                                                                                                                                       |
| 3.1.3      | 2021.04.29 | Task    | Pass arguments in signal as reference                                                                                                                                                            |
| 3.1.2      | 2021.03.17 | Task    | Add extension key to composer.json                                                                                                                                                               |
| 3.1.1      | 2021.01.19 | Bugfix  | Prevent exception on missing links in middleware                                                                                                                                                 |
| 3.1.0      | 2021.01.10 | Feature | Autoreleases to TER added, small bugfix with deleted receivers                                                                                                                                   |
| !!! 3.0.0  | 2020.12.17 | Feature | templateRootPaths for content element rendering increased from 10 to 100 - please update your TypoScript! Signal added, show correct number of receivers, Some other smaller bugfixes            |
| 2.4.0      | 2020.07.10 | Feature | Settings and variables can be used via TS, fix possible charset and parsing problems                                                                                                             |
| 2.3.0      | 2020.05.10 | Task    | Support lux 8.0.0 now                                                                                                                                                                            |
| 2.2.2      | 2020.04.23 | Bugfix  | Fix problem "Table tx_luxletter_domain_model_user doesn't exist" in links from newsletters                                                                                                       |
| 2.2.1      | 2020.04.22 | Bugfix  | Fix CSS class in backend module, fix possible problem with template orderings                                                                                                                    |
| 2.2.0      | 2020.04.20 | Task    | Update for TYPO3 10.4 LTS                                                                                                                                                                        |
| 2.1.0      | 2020.03.29 | Feature | User real receiver name in mails, Add API functions to send existing newsletters to new registered users                                                                                         |
| 2.0.1      | 2020.03.23 | Bugfix  | Prevent exception direct after a new installation when configuration was not yet changed                                                                                                         |
| 2.0.0      | 2020.03.21 | Task    | Update for TYPO3 10 and lux 7, Add widgets to TYPO3 dashboard, Support Mailmessage in TYPO3 9+10                                                                                                 |
| 1.2.3      | 2020.03.19 | Task    | Pass value by reference in signal to change newsletter content                                                                                                                                   |
| 1.2.2      | 2019.12.11 | Bugfix  | Don't stop sending if there are users without email address in the receiver group                                                                                                                |
| 1.2.1      | 2019.11.26 | Bugfix  | Fix problem on packagist.org                                                                                                                                                                     |
| 1.2.0      | 2019.11.26 | Task    | Show helpful messages in some exceptional cases. Use mediumtext for bodytext for more space now.                                                                                                 |
| 1.1.1      | 2019.09.19 | Bugfix  | Don't throw an exception for empty fe_users.crdate fields                                                                                                                                        |
| 1.1.0      | 2019.08.20 | Bugfix  | Some css fixes, Fix for default image and small code cleanup                                                                                                                                     |
| 1.0.0      | 2019.08.02 | Task    | First stable TER release with a useful documentation                                                                                                                                             |
| 0.3.0      | 2019.07.31 | Task    | Support for lux, Add signal, Receiver module                                                                                                                                                     |
| 0.2.0      | 2019.07.13 | Task    | Fix for PHP 7.3, Fix for default sql mode setting, documentation update                                                                                                                          |
| 0.1.0      | 2019.07.10 | Task    | Initial release of a working newsletter extension                                                                                                                                                |


## Breaking changes !!!

### Upgrade to 18.x

* Some TypoScript changes were necessary for the TYPO3 12 update (because switchableControllerActions were removed)
  * We renamed all settings from `tx_luxletter_fe` to `tx_luxletter` to be able to use the TypoScript in all plugins
  * **Todo:** Change your TypoScript settings (see example below)

Example:

```
plugin {
  # Former: tx_luxletter_fe
  tx_luxletter {
    settings {
      containerHtml {
        path = EXT:yoursitepackage/Resources/Private/Templates/Mail/
        options {
          1 {
            label = LLL:EXT:yoursitepackage/Resources/Private/Language/locallang_db.xlf:newsletter.layouts.1
            fileName = MyContainer1
          }
        }
      }
    }
  }
}
# Former: module.tx_luxletter < plugin.tx_luxletter_fe
module.tx_luxletter < plugin.tx_luxletter
```

### Upgrade to 26.x

* Multiclient availability needs reading access for editors to rootpages of relevant sites
  * **Todo:** Check permissions for your editors on some root pages - see [Editors configuration](../Editors/Index.md) for details
* Also reading access to pages where relevant records are stored is needed now for editors
  * **Todo:** Check permissions for your editors for some records - see [Editors configuration](../Editors/Index.md) for details

### Upgrade to 17.x

* Multiple user groups can now be used for selecting receivers for newsletters
  * So the fieldname changed from `tx_luxletter_domain_model_newsletter.receiver` to `.receivers`
  * To allow sending of old newsletters that are not yet completely sent (or to edit afterwards), the value must be copied from one field to the other
  * **Todo:** Simply run the upgrade wizard (e.g. `./vendor/bin/typo3cms upgrade:run luxletterReceiversUpdateWizard`)
* If newsletters are added via CLI command, you can now set a category uid
  * Example call (see third last parameter for category uid) `./vendor/bin/typo3 luxletter:createnewsletterfromorigin "Automatic NL" 1 1 16 0 "NewsletterContainer" "Newsletter {f:format.date(date:'now',format:'Y-m')}" 123 "Optional description here" "2022-12-24T14:00:00+00:00"`
  * **Todo:** Adjust your CLI commands or your scheduler tasks if you want to add a category

### Upgrade to 13.x

* If you have extended luxletter via slots (signalslots), you have to adjust your code by using PSR-14 eventdispatchers now
  * Because we wanted to reduce technical debt and because making future updates for TYPO3 12 easier, we already switched to eventdispatchers
  * All places that were extended with signals are now extended with eventdispatchers
  * See official documentation how to use eventlisteners https://docs.typo3.org/p/brotkrueml/schema/main/en-us/Developer/Events.html
  * **Todo:** Replace your slots in your extension for luxletter with events

### Upgrade to 11.x

* Definition of Container filenames in TypoScript has changed a bit - default layout name is `NewsletterContainer`
  * Because we now support multilanguage configuration, it's possible to add layout files per language.
  * Now we don't need to add `.html` to the definition
  * **Todo:** Remove the extension in TypoScript setup: `plugin.tx_luxletter_fe.settings.containerHtml.options.1.fileName=NewsletterContainer.html` => `plugin.tx_luxletter_fe.settings.containerHtml.options.1.fileName=NewsletterContainer`
* Command `luxletter:createnewsletterfromorigin`
  * has a new parameter "language" now
  * has a different ordering of parameters now
  * **Todo:** If you are using this command, update your scheduler task or CLI command (see documentation)
* Signal `\In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl::constructor`
  * has only one parameter now: NewsletterUrl object (with getter and setter for uri, origin and language)
  * **Todo:** If you are using this signal, adjust your slot


### Upgrade to 8.x

Container HTML template is now configured differently to allow a selection from editors.
While it was before 8.0 used from `templateRootPath/Mail/NewsletterContainer.html`, we can now configure a path with
more than one container template via TypoScript:

```
plugin {
  tx_luxletter_fe {
    settings {
      # Define container.html files
      containerHtml {
        path = EXT:yoursitepackage/Resources/Private/Templates/Mail/
        options {
          1 {
            label = LLL:EXT:yoursitepackage/Resources/Private/Language/locallang_db.xlf:newsletter.layouts.1
            fileName = MyContainer1.html
          }
          2 {
            label = My Layout 2
            fileName = MyContainer2.html
          }
        }
      }
    }
  }
}
```

**Note:** Already existing newsletters without new layout property will not be send any more!


### Upgrade to 5.x

This is only a small breaking change. CSS files are included via TypoScript now. So we removed the partial ZurbCss.html
and put all css now to EXT:luxletter/Resources/Private/Css/.
Todo: Remove the partial call in NewsletterContainer.html template file.


### Upgrade to 4.x

Multiple senders can now be defined in records, in addition it's not needed to define a domain in extension configuration
any more. We now look into site configuration. But that change needs you to adjust some stuff.

Breaking changes in detail and what you have to do step by step after you have updated the extension:

* Add one (ore more) record(s) with sender information on any sysfolder
* Update your site configuration with an unsubscribe pid (so you could use different unsubscribe plugins now)
* Take care that your base (Entry point) settings in site configuration is not just `/` but a full domain prefix like `https://www.domain.org/`
* Create new newsletter records with the new sender configuration. **Note:** Old newsletters won't be queued any more because a sender configuration is missing
* Update your HTML template files (compare your files with `EXT:luxletter/Resources/Private/Templates/Mail/NewsletterContainer.html`)
    * If you are using the viewhelper `luxletter:mail.getUnsubscribeUrl` now another argument must be passed: `site` - example: `{luxletter:mail.getUnsubscribeUrl(newsletter:newsletter,user:user,site:site)}`
    * If you are usint the viewhelper `luxletter:configuration.getDomain` also `site` must be passed as argument - example: `{luxletter:configuration.getDomain(site:site)}`
    * If you have changed the TrackingPixel.html partial file, take also care that `site` is now passed to `luxletter:mail.getTrackingPixelUrl` - example: `{luxletter:mail.getTrackingPixelUrl(newsletter:newsletter,user:user,site:site)}`
