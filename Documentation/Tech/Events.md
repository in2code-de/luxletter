<img align="left" src="../../Resources/Public/Icons/lux.svg" width="50" />

# Luxletter - Email marketing in TYPO3. Send newsletters the easy way.

## Events

There are many events that can be used to extend Luxletter.
This documentation is under construction and not all events are documented yet.

### AfterTestMailButtonClickedEvent

This event can be used to deactivate the internal sending of test emails and implement your own logic, e.g. sending test newsletters via a separate queue.

To deactivate the internal logic, the `$testMailIsSendExternal` property of the event must be set to true.
If the general status `$status` is set to `false`, no message is shown.
If the general status `$status` is set to `true`, a message with the properties `$statusTitle`, `$statusMessage` and `$statusSeverity` is shown.
The values for `$statusSeverity` can be `AfterTestMailButtonClickedEvent::STATUS_SEVERITY_SUCCESS`, `AfterTestMailButtonClickedEvent::STATUS_SEVERITY_WARNING` and `AfterTestMailButtonClickedEvent::STATUS_SEVERITY_ERROR`, the default value is `AfterTestMailButtonClickedEvent::STATUS_SEVERITY_ERROR`.

The `$request` property is available in the event, from which all necessary data can be obtained to send the test e-mail.

Sample Eventlistener:

```
<?php

declare(strict_types=1);
namespace Vendor\Extension\EventListener;

use In2code\Luxletter\Events\AfterTestMailButtonClickedEvent;

final class DemoEventlistener
{
    public function __invoke(AfterTestMailButtonClickedEvent $event): void
    {
        $event->setTestMailIsSendExternal(true);
        // ... handle email sending
        $event->setStatus(true);
        $event->setStatusSeverity(AfterTestMailButtonClickedEvent::STATUS_SEVERITY_SUCCESS);
        $event->setStatusTitle('Success');
        $event->setStatusMessage('The test email is successfully added to the queue');
    }
}
```

### BeforeBodytextIsParsedEvent

This event is executed before the body text for the individual newsletter is processed. The event can be used to change or reset the body text that is used for parsing.

The `$queue` property is available in the event, which can be used to access all relevant data for the newsletter.
If the `$bodytext` property is set via an event listener, the content of this property is used for further processing. If there is no content in the property, the standard text from the newsletter is used.

Sample Eventlistener:

```
<?php

declare(strict_types=1);
namespace Vendor\Extension\EventListener;

use In2code\Luxletter\Events\AfterTestMailButtonClickedEvent;
use In2code\Luxletter\Events\BeforeBodytextIsParsedEvent;

final class DemoEventlistener
{
    public function __invoke(BeforeBodytextIsParsedEvent $event): void
    {
        $event->setBodytext('<h1>I am the new body</h1>');
    }
}

```
