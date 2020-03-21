<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

# Luxletter - Email marketing in TYPO3. Send newsletters the easy way.


## Tech corner or extending luxletter

There are some possibilities to extend luxletter.
All HTML-Templates (and Partials and Layouts) can be overwritten by your extension in the way how templates can
be overruled in TYPO3. This fits the own content element `Teaser` and the rendering for the FluidStyledMailContent
elements as well as the templates for the backend modules of luxletter and the `NewsletterContainer.html`.

If you want to manipulate the PHP, there are a lot of signals added to the extension itself. Just search for
`signalDispatch`. You will find a lot of methods where you can stop mail sending, manipulate values, etc...


## FAQ


### Do I need to install lux?

No, luxletter works without the extension lux but you can also install the free extension lux. 
After that, you can also use the Receiver action in the backend module to see some usefull information about the 
receiver activities.


### What is lux?

Lux is a free marketing automation tool as a TYPO3 extension and a perfect fit for luxletter.


### Can I use a third party mailserver in luxletter?

Of course, you can. Look at [Installation](../Installation/Index.md) to see a configuration example.


### What about HTML for mailings?

Luxletter uses FluidStyledMailContent to render some content elements in an oldschool HTML-style for a wide bunch
of mail clientes.


### Can I use tt_address for my receivers?

No, not at the moment. We focused on fe_users.


### What about bounce mail handling?

At the moment there is no bounce mail handling integrated.

### Scheduler task is failing while sending a newsletter

Look at the sys log to see which problem caused this issue. E.g. if fe_users.crdate is empty, etc...

### Mail sending is too slow

Check how many mails can be sent per hour. Ask your hoster. Modify the queue settings.

### Mail could not be parsed in preview when adding an origin

This could have different reasons:

#### 1. Type definition is missing in site configuration

If you define any type-parameters in your site configuration, you have to define also the types for luxletter:

| Type | Explanation |
|------|-------------|
| 1560777975 | Needed for a prerendering |
| 1562349004 | Needed for the newsletter rendering |
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
