<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

# Luxletter - Email marketing in TYPO3. Send newsletters the easy way.

## Change templates

Change templates path in TypoScript setup via your sitepackage extension (e.g. EXT:sitepackage) -
`EXT:sitepackage/Configuration/TypoScript/setup.typoscript`:

```
plugin {
    tx_luxletter {
        view {
            templateRootPaths {
                0 = EXT:luxletter/Resources/Private/Templates/
                1 = EXT:lux/Resources/Private/Templates/
                2 = EXT:sitepackage/Resources/Private/Templates/
            }
            partialRootPaths {
                0 = EXT:luxletter/Resources/Private/Partials/
                1 = EXT:lux/Resources/Private/Partials/
                2 = EXT:sitepackage/Resources/Private/Partials/
            }
            layoutRootPaths {
                0 = EXT:luxletter/Resources/Private/Layouts/
                2 = EXT:sitepackage/Resources/Private/Layouts/
            }
        }

        settings {
            addInlineCss {
                0 = EXT:luxletter/Resources/Private/Css/ZurbFoundation.css
                1 = EXT:luxletter/Resources/Private/Css/Luxletter.css
                2 = EXT:sitepackage/Resources/Private/Css/Luxletter.css
            }

            # Define container.html files
            containerHtml {
                path = EXT:sitepackage/Resources/Private/Templates/Mail/
                options {
                    1 {
                        # "NewsletterContainer" means:
                        # "NewsletterContainer.html" in default language or
                        # "NewsletterContainer_de.html" in german language and so on...
                        fileName = NewsletterContainer
                        label = Layout 1
                    }
                    2 {
                        fileName = NewsletterContainer2
                        label = LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:newsletter.layouts.1
                    }
                }
            }
        }
    }
}
module.tx_luxletter < plugin.tx_luxletter
```

**Note:** If you change the path via TypoScript extension template, please take care that you are using the very first
template on root (otherwise the paths could not be recognized by the backend module or CLI calls)

Next copy the template file NewsletterContainer.html to your sitepackage in
`EXT:sitepackage/Resources/Private/Templates/Mail/` and modify it a bit with your wanted HTML.

Now you can include the file with a **ext_typoscript_setup.typoscript** file
(that is **important** to include the TypoScript after the TypoScript of luxletter):

`@import 'EXT:sitepackage/Configuration/TypoScript/setup.typoscript'`

**Note:** The ordering of the TypoScript is the key solution to change the template files - check this in the TypoScript Object Browser

**Note:** If your TypoScript is still overruled by the default TS, ensure that luxletter is loaded before your sitepackage (via ext_emconf.php depends settings in your sitepackage)

## Extending luxletter

There are some possibilities to extend luxletter.
All HTML-Templates (and Partials and Layouts) can be overwritten by your extension in the way how templates can
be overruled in TYPO3. This fits the own content element `Teaser` and the rendering for the FluidStyledMailContent
elements as well as the templates for the backend modules of luxletter.

If you want to manipulate the PHP, there are a lot of PSR-14 eventdispatchers added to the extension itself.
Just search for `eventDispatcher->dispatch`.
You will find a lot of methods where you can stop mail sending, manipulate values, etc...
Read more about how to use eventlisteners in the official TYPO3 documentation:
https://docs.typo3.org/p/brotkrueml/schema/main/en-us/Developer/Events.html


## Add new users to the queue

If you want to implement in your frontend user registration that new users with a frontenduser group are automatically
get the latest or a defined newsletter, there are 2 API functions in luxletter, that can be used for this stuff.

```
# Add fe_users.uid=123 to the queue and send him the latest newsletter
$queueService = GeneralUtility::makeInstance(\In2code\Luxletter\Domain\Service\QueueService::class);
$queueService->addUserWithLatestNewsletterToQueue(123);
```

```
# Add fe_users.uid=123 to the queue and send him newsletter with uid 234
$queueService = GeneralUtility::makeInstance(\In2code\Luxletter\Domain\Service\QueueService::class);
$queueService->addUserWithNewsletterToQueue(123, 234);
```


## FAQ


### Do I need to install lux?

No, luxletter works without the extension [Lux](https://www.in2code.de/produkte/lux-typo3-marketing-automation/) but
you can additionally add the free extension lux.
After that, you can also use the Receiver action in the backend module to see some usefull information about the
receiver activities.


### What is lux?

[Lux](https://www.in2code.de/produkte/lux-typo3-marketing-automation/) is a free marketing automation tool
as a TYPO3 extension and a perfect fit for luxletter.


### Can I use a third party mailserver in luxletter?

Of course, you can. Look at [Installation](../Installation/Index.md) to see a configuration example.


### What about HTML for mailings?

Luxletter uses FluidStyledMailContent to render some content elements in an oldschool HTML-style for a wide bunch
of mail clientes.


### Can I use tt_address for my receivers?

No, not at the moment. We focused on fe_users - a technique nearby the core.


### What about bounce mail handling?

At the moment there is no bounce mail handling integrated.


### Scheduler task is failing while sending a newsletter

Look at the sys log to see which problem caused this issue. E.g. if fe_users.crdate is empty, etc...


### Mail sending is too slow

Check how many mails can be sent per hour. Ask your hoster. Modify the queue settings. Embedding images can slow down
the mail performance.


### Mail could not be parsed in preview when adding an origin

This could have different reasons:

#### 1. Type definition is missing in site configuration

If you define any type-parameters in your site configuration, you have to define also the types for luxletter:

| Type       | Explanation                                   |
|------------|-----------------------------------------------|
| 1560777975 | Needed for a prerendering                     |
| 1562349004 | Needed for the newsletter rendering           |
| 1561894816 | Type for tracking pixel (neede for open rate) |

Example configuration:

```
...
rootPageId: 1
routes:
  -
    route: robots.txt
    type: staticText
    content: "Disallow: /typo3/\r\n"
routeEnhancers:
  PageTypeSuffix:
    type: PageType
    default: /
    index: ''
    suffix: /
    map:
      preview.html: 1560777975
      newsletter.html: 1562349004
      pixel.png: 1561894816
...
```

#### 2. TypoScript for Fluid Styled Mail Content is missing

Just add the TypoScript for Fluid Styled Mail Content in static template
(but don't forget to add it at the root template)


#### 3. No valid ssl certificate available

E.g. on a local develop machine it could be possible to not have a valid SSL certificate. You could disable the ssl
check for testing globally in TYPO3
(see https://www.in2code.de/en/recent/php-ignore-ssl-certificates-in-curl-requests-in-typo3/).


#### 4. The target URL can not be parsed by your webserver

For some reasons your infrastructure does not allow your server to build request to it's own websites (htpasswd cover,
no ssl certificate, page to parse is a "backend user section" application login would be needed, etc...).

Tip: You could test the server requests by yourself with a curl command on the server bash like
`curl -I https://domain.org/2022-01/newsletter.html`
what should result in a status code 200.


### Images are not loaded in my Newsletter Mail

If you are using `fluidStyledMailContent` luxletter will set `config.absRefPrefix` to the configured
domain automaticly. If you are using your own rendering typenum, you have to set absRefPrefix manually.


### Embedding images or not?

Embedding images can be activated in the main extension configuration. All images are recognized automatically and will
be embedded.

#### The upside

Embedding images allows you to send newsletters from a secured instance (e.g. an Intranet). In addition most mail
clients will show the images without asking the using. On top processed images may not exists any more on the server
when temp images are deleted - but this would have no negative effect im images are embedded.

#### The downside

Embedding will slow down the mail sending process. The mail itself is - of course - a lot of bigger then sending mails
without images.

### Readable URL for unsubscribe links would be nice - any hints?

You can use RouteEnhancers to rewrite the unsubscribe links. An example configuration would be:

```
routeEnhancers:
  LuxletterUnsubscribe:
    type: Extbase
    extension: Luxletter
    plugin: Fe
    defaultController: 'Frontend::unsubscribe'
    routes:
      - routePath: '/u/{user}/{hash}/{newsletter}'
        _controller: 'Frontend::unsubscribe'
        _arguments:
          user: 'user'
          hash: 'hash'
          newsletter: 'newsletter'
        requirements:
          hash: '^[a-zA-Z0-9]{64}$'
          newsletter: '[0-9]{1,3}'
          user: '[0-9]{1,5}'
    aspects:
      user:
        type: PersistedAliasMapper
        tableName: fe_users
        routeFieldName: uid
      hash:
        type: StaticUnsubscribeHashMapper
        routeFieldName: hash
      newsletter:
        type: PersistedAliasMapper
        tableName: tx_luxletter_domain_model_newsletter
        routeFieldName: uid
```

As aspect an own one is used:
```
<?php
namespace YourVendorname\YourSitepackage\\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;

class StaticUnsubscribeHashMapper implements StaticMappableAspectInterface
{
    /**
     * @param string $value
     * @return string|null
     */
    public function generate(string $value): ?string
    {
        return $this->respondWhenValidSha256Hash($value);
    }

    /**
     * @param string $value
     * @return string|null
     */
    public function resolve(string $value): ?string
    {
        return $this->respondWhenValidSha256Hash($value);
    }

    /**
     * @param string $value
     * @return null|string
     */
    protected function respondWhenValidSha256Hash(string $value): ?string
    {
        if (preg_match('/^[a-fA-F0-9]{64}$/', $value)) {
            return $value;
        }

        return null;
    }
}
```

...and registered via `ext_localconf.php`:
```
$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['StaticUnsubscribeHashMapper'] =
    \YourVendorname\YourSitepackage\Routing\Aspect\StaticUnsubscribeHashMapper::class;
```
