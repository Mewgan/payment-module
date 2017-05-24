## Payment Module

Un module de paiement pour webzy avec Stripe

### Dependencies

Stripe is required to pay 
```bash
$ composer require stripe/stripe-php
```

WKHTMLtoPDF is required to generate pdf
```bash
wget http://download.gna.org/wkhtmltopdf/0.12/0.12.2.1/wkhtmltox-0.12.2.1_linux-wheezy-amd64.deb
dpkg -i wkhtmltox-0.12.2.1_linux-wheezy-amd64.deb
# Si il vous manque des dépendances
apt-get -f install
# for php
composer require mikehaertl/phpwkhtmltopdf
```