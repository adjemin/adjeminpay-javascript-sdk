
# [AdjeminPay](https://www.adjeminpay.com) Seamless Integration

## adjeminpay-javascript-sdk

Seamless javascript integration of e-payment for websites with AdjeminPay
AdjeminPay Seamless Integration permet d'intégrer facilement AdjeminPay de façon transparente à son service en ligne, c'est à dire que le client effectue le paiement sans quitter le site
du marchand.

<!-- ## Compatibilité Application Hybride

AdjeminPay Seamless Integration a été testé et fonctionne sur :

* Cordova
* phoneGap
* Ionic
* jQuery Mobile -->
L'intégration de ce SDK se fait en trois étapes :

## Etape 1 : Page de notification

Pour ceux qui possèdent des services qui ne neccessitent pas un traitement des notifications de paiement de AdjeminPay, vous pouvez passer directement à l'etape 2, par exemple les services de don.

A chaque paiement, AdjeminPay vous notifie via un lien de notification, nous vous conseillons de toujours le traiter côté serveur. Nous allons utiliser PHP dans ce cas de figure :
Script index.php dans <http://mondomaine.com/notify/> (le script doit se trouver dans le repertoire de votre url notify_url) ;

```php
<?php

    // EXEMPLE DE NOTIFY --
?>
```

## Etape 2 : Formulaire de paiement

Avant de commencer cette etape, il faut lier le seamless SDK à votre page :

* `https://www.adjeminpay.com/release/seamless/latest/adjeminpay.prod.min.js`    : si vous êtes en production

Cela se fait dans la balise head de votre page web

Exemple (en PROD) :

```html
   <head>
       ...
       <script charset="utf-8"
               src="https://www.adjeminpay.com/release/seamless/latest/adjeminpay.prod.min.js"
               type="text/javascript">
       </script>
   </head>
```

### Formulaire AdjeminPay

Le formulaire de paiement AdjeminPay est constitué de :

* `amount`      : Montant du paiement
* `currency`    : Devise du paiement, toujours en CFA pour le moment
* `trans_id`    : L'identifiant de la transaction, elle est unique
* `designation` : La designation de votre paiement
* `notify_url`  : le lien de notification silencieuse (IPN) après paiement

Vous pouvez ajouter en option ces deux elements :

* `phone_num`      : Numéro de téléphone sur lequel l'utilisateur effectuera le paiement
* `adp_phone_prefixe`    : Code Pays du numéro de téléphone (exemple 225)

Exemple :

```html
<p id="payment_result"></p>
<form id="info_paiement">
    <input type="hidden"  id="amount" value="10">

    <input type="hidden" id="currency" value="CFA">

    <input type="hidden" id="trans_id" value="">

    <input type="hidden" id="adp_custom" value="">

    <input type="hidden" id="designation" value="Achat de chaussure noir">

    <button type="submit" id="process_payment">Proceder au Paiement</button>
</form>
```

NB : _Avant l'affichage de ce formulaire, vous devez enregistrer les informations concernant cette transaction dans votre base de données afin de les verifier après paiement du client_

#### Lier le formulaire au SDK Javascript

Sur clic du bouton "Proceder au Paiement", Nous allons recuperer le montant de la transaction, l'identifiant de la transaction, la devise, la désignation et l'url de notification pour debuter le processus de paiement transparent sur AdjeminPay
Exemple (fichier payment.js) :

```html
<script >
    AdjeminPay.init({
            apikey: '174323661757617531bf99c9.80613927',
            application_id: 393509,
            notify_url: 'http://mondomaine.com/notify/'
        });
    var process_payment = document.getElementById('process_payment');
        process_payment.addEventListener('click', function () {
            AdjeminPay.preparePayment({
                amount: parseInt(document.getElementById('amount').value),
                trans_id: document.getElementById('trans_id').value,
                currency: document.getElementById('currency').value,
                designation: document.getElementById('designation').value,
                custom: document.getElementById('adp_custom').value
            });
            AdjeminPay.renderPaymentView();
        });
</script>
```

## Etape 3 : Observer  le paiement transparent

Lorsque le client se trouve sur le guichet de AdjeminPay, Vous pouvez suivre l'etat d'avancement du client sur AdjeminPay grace à ces evènements :

* `error`              : Une erreur s'est produite, les requëtes ajax ou le paiement ont echoué,
* `paymentPending`     : Le paiement est en cours
* `paymentSuccessfull` : Le paiement est terminé, Le paiement est valide ou est annulé

Exemple (suite du fichier payment.js):

```html
<script >
   var result_div = document.getElementById('payment_result');
   AdjeminPay.on('error', function (e) {
        result_div.innerHTML = '';
        result_div.innerHTML += '<b>Error code:</b>' + e.code + '<br><b>Message:</b>:' + e.message;
   });
   AdjeminPay.on('paymentPending', function (e) {
       result_div.innerHTML = '';
        result_div.innerHTML = 'Paiement en cours <br>';
        result_div.innerHTML += '<b>code:</b>' + e.code + '<br><b>Message:</b>:' + e.message;
   });
   AdjeminPay.on('signatureCreated', function () {})
   AdjeminPay.on('paymentSuccessfull', function (paymentInfo) {

            if(paymentInfo.adp_result == 'SUCCESS'){
                result_div.innerHTML = 'Votre paiement a été validé avec succès : <br> Montant payé :'+paymentInfo.adp_amount+'<br>';
            }else{
                result_div.innerHTML = 'Une erreur est survenue :'+paymentInfo.adp_error_message;
            }
   });
</script>
```

<!-- ## Compatibilité Navigateurs Web

AdjeminPay Seamless Integration a été testé et fonctionne sur tous les navigateurs modernes y compris :

* Chrome
* Safari
* Firefox
* Opera
* Internet Explorer 8+. -->

<!-- ## Votre Api Key et Site ID -->

<!-- Ces informations sont disponibles dans votre BackOffice AdjeminPay. -->

<!-- ## Exemple Intégration -->

<!-- Vous trouverez un exemple d'intégration complet dans le dossier exemple/html/ -->
