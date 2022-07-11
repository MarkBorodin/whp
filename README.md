Installation:
Add folder WHPBundle to plugins folder.
Add folder WebhookBundle along path app/bundles/ with replacement

Execute commands:

bin/console cache:clear --env=prod
bin/console mautic:plugins:reload
bin/console doctrine:schema:update --force

Navigate to /s/plugins (Configuration > Plugins ) and enable BeeFree integration.

Usage:

Fill in the required fields.
Select "Use premium features" - "Yes"
Click "Add a received pair" and write received data. Received field - field in the data we receive. Subject field - field to be entered from 'Received field.
The available fields for contacts and companies are available next to the fields so that they can be copied. It is important that they are named exactly as they are shown in this list.