
# [AdjeminPay](https://www.adjeminpay.com) Seamless Integration

## adjeminpay-javascript-sdk

AdjeminPay Seamless Integration permet d'intégrer les services AdjeminPay rapidement à sa platforme, afin que le client puisse effectuer un paiement sans quitter le site
du marchand.

L'intégration de ce SDK se fait en trois étapes :

## Etape 1 : Page de notification

Lors de vos paiements, AdjeminPay vous notifie via une uri que vous avez précédement définie dans votre interface admin lors de la création de votre application. Dans l'éventualité où vous n'avez pas encore passer cette étape je vous conseillerez de créer un application dans votre interface puis suivre la suite.

## Etape 2 : Formulaire de paiement

Commencez par lier le seamless SDK à votre page, vous trouverez le js à l'adresse :

`https://www.adjeminpay.com/release/seamless/latest/adjeminpay.prod.min.js`

l'intégration du lien ce fais comme suit :

```html
<script src="https://www.adjeminpay.com/release/seamless/latest/adjeminpay.prod.min.js" type="text/javascript"></script>
```

### Information sur la transaction AdjeminPay

Pour faire une transaction avec AdjeminPay vous devez definir les champs suivant :

* `amount`      : Montant du paiement
* `currency`    : Devise du paiement, en CFA
* `trans_id`    : Unique identifiant de la transaction
* `designation` : Designation du paiement
* `notify_url`  : uri de notification ou vous recevrez les information après le paiement

Ces éléments sont facultatifs :

* `phone_num`      : Numéro de téléphone utiliser pour le paiement
* `adp_phone_prefixe`    : CC ou Country Code du numéro de téléphone utiliser 
    * exemple : (225 => pour la côte d'ivoire) 

Exemple :

```html
<p id="result"></p>
<form id="paiement">
    <input type="hidden"  id="amount" value="7500">

    <input type="hidden" id="currency" value="CFA">

    <input type="hidden" id="trans_id" value="">

    <input type="hidden" id="adp_custom" value="">

    <input type="hidden" id="designation" value="Ecouteur vert bluetooth">

    <button type="submit" id="requestToPay">Faire un Paiement</button>
</form>
```

NB : _Enregistrer au préalable dans votre base de donnée (BD) les informations concernant une conversation pour pouvoir faire une comparaison plutard_

#### Lier le formulaire au SDK Javascript

Cliquez sur "Faire un Paiement" pour commencer, nous ferons ensuite en background un enregistrement en prenant les différents champs puis nous vous notifierons sur l'url de notification. Pour avoir un aperçu sur comment cela se faire regarder l'exemple suivant :
```js
    AdjeminPay.init({
        apikey: 'VOTRE_API_KEY',
        application_id: 'VOTRE_APPLICATION_ID',
        notify_url: 'URL_NOTIFICATION'
    })
    $('requestToPay').click(function () {
        AdjeminPay.preparePayment({
            amount: parseInt($('#amount').val()),
            trans_id: $('#trans_id').val(),
            currency: $('#currency').val(),
            designation: $('#designation').val(),
            custom: $('#adp_custom').val()
        });
        AdjeminPay.renderPaymentView();
    });
```
NB: _Pour cet exemple nous avons utilisé jquery et le code précédent se fait à l'intérieur d'une balise `<script></script>`_


## Etape 3 : Ecouter les evenements qui se produisent lors de notre Observer

Quand le client se trouve sur l'interface de paiement AdjeminPay, Vous avez la possibilité de suivre l'état d'avancement de celui-ci par le biais des evènements.
Quelques evenements qui se produisent :

* `error`              : Pour nous signaler des erreurs qui se sont produitent, dont les requëtes ajax ou le paiement à échouer,
* `paymentPending`     : Pour nous signaler d'un paiement en cours
* `paymentSuccessfull` : Pour nous signaler d'un paiement est terminé, soit validé ou est annulé

Exemple : 

```js
   AdjeminPay.on('error', function (e) {
        $('#result').empty()
        $('#result').html(`<b>Error code:</b>${e.code}<br><b>Message:</b>:${e.message}`)
   });
   AdjeminPay.on('paymentPending', function (e) {
        $('#result').empty()
        $('#result').html('Paiement en cours <br><b>code:</b>${e.code}<br><b>Message:</b>:${e.message}')
   })
   AdjeminPay.on('signatureCreated', function () {})
   AdjeminPay.on('paymentSuccessfull', function (info) {
        if(paymentInfo.adp_result == 'SUCCESS'){
            $('#result').html(`Votre paiement a été validé avec succès : <br> Montant payé : ${info.adp_amount+}<br>`)
        }else{
            $('#result').html(`Une erreur est survenue : ${info.adp_error_message}`)
        }
   })
```
NB: _ce code est la suite du précédent code JS._
