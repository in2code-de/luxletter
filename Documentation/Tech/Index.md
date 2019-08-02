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
