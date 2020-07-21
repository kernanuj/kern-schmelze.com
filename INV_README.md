
# Plugins
## InvExportLabel

### 

1. Create cron job running once a day to export labels, example
>bin/console inv:export-label:export --daysBack=1 --daysForward=0 --type=inv_mixer_product


#### orders considered in cron job

For an order to be considered in the export, the following must match
- Order must contain at least one item of the defined type; for now it is only InvMixerProductItem
- Order must have a transaction update (paid etc) within the timeframe given as arguments to the command (-1day to + 2 days for example)
- Order must match a configured state combination; the configuration is done in the plugin settings. Each state combination consists of
    - order state (the overall order)
    - transaction state (payment)
    - delivery state
    - example : `order:open, transaction:paid, delivery:open`
