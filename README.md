Bank Integration
================

From 2009 until 2014, Kinento has sold the Magento Bank Integration module for 229 euro per license. From 2015 onwards, updates are no longer provided by Kinento, but the module is instead available on GitHub free of charge. The code is released under the MIT open-source license and community patches and feature additions are encouraged.

Included is the original PDF manual (manual.pdf) and the Magento Connect description (below).

Module description
================

With Kinento's Bank Integration module, transactions from your bank account are automatically coupled to Magento's order system. Bank Integration searches for paid orders and updates the order status according to the transaction amount, processing payments automatically: You'll never have to check for paid orders manually anymore! 

Automatic bank-to-Magento coupling
-------------

With Bank Integration, the coupling of bank transactions and unpaid orders is based on both the bank description and the transaction amount. When handling with insufficient data, Bank Integration provides an easy to use manual coupling system as a back-up. Payments made through your bank will be visible in Magento's back-end, giving a complete order/payment overview. 

Bank support
-------------

If your bank includes an export-to-file feature, Bank Integration can automatically couple bank transactions to orders. The module can be customized to support your bank (see Bankintegration/Model/Banks.php). 

Bank Integration: since 2009
-------------

Bank Integration has been customized, fine-tuned, and updated since its first version in 2009. It includes wide compatibility, added functionality, and many configuration options, all part of customer's feature requests since 2009.

Main features of Bank Integration
-------------

General: 
- Automatic coupling between orders in the Magento database and your bank's transactions. 
- Order statusses change after coupling according to your bank-data. 
- Gives an overview of your bank-data in the Magento environment. 

Automatic coupling: 
- Coupling is based on the amount and the order ID. 
- For unclear bank-data, the module makes a 'best guess'. 
- Unmatched orders can be coupled manually through the module. 
- Automatically send an email upon succesful coupling. 

Customizable filters: 
- Unrelated bank data can be automatically neglected using filters. 
- Filters can be set to different fields, depending on the user's wishes. 
- Automatically filter negative amounts (setting). 

Fine-tuning options: 
- Fine-tune the module to your own invoice numbering scheme. 
- Select which orders should and should not be considered for coupling. 
- Select the status in which your order should change after coupling. 
- Add bankdata manually. 

Visualisation and feedback: 
- Show the per-order paid amount in percentages. 
- Show bank payments when viewing order data. 
- Export (coupled) bankdata to .CSV file. 

Locale: 
- Translation files provided in English and German. 

Bank Support in version 2.0.1
-------------

- ING Bank (NL) 
- ING Bank alternative (NL) 
- Rabobank (NL) 
- ABN Amro (NL) 
- VR-Bank (DE) 
- Postbank (DE) 
- Postbank alternative (DE) 
- UniCredit (DE) 
- Deutsche Bank (DE) 
- Kreissparkasse (DE) 
- Outbank (DE) 
- DAB Munchen (DE) 
- Bank X (DE) 
- Hibiscus (DE) 
- Commerzbank (DE) 
- Credit Suisse V11 (CH) 
- Swiss Postfinance (CH) 
- Erste Bank (AT) 
- Raiffaisen (HU) 
- UniCredit (HU) 
- Osuuspankki (FI) 
- Rietumu (LV) 
- Multibank (PL) 
- Bank of America (US) 
- BBVA Bancomer (MX) 
- Banco Monex (MX) 
- CIMB Bank (MY) 
- HSBC Bank (MY) 
- Maybank (MY) 
- MT942/MT940 
- ISO20022 
- ISO20022 (camt.054) 
