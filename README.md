# [AdjeminPay](https://www.adjeminpay.com) Seamless Integration

## adjeminpay-javascript-sdk

AdjeminPay Seamless Integration permet d'intégrer les services AdjeminPay rapidement à votre platforme, afin que le client puisse effectuer un paiement sans quitter le site
du marchand.

L'intégration de ce SDK se fait en trois étapes :

## Etape 1 : Intercepter les notifications des transactions au niveau votre serveur

Lors de vos paiements, AdjeminPay vous notifie via une uri que vous avez précédement définie dans votre interface admin lors de la création de votre application. Dans l'éventualité où vous n'avez pas encore passer cette étape je vous conseillerez de créer un application dans votre interface puis suivre la suite.

Sur votre serveur utilisez [notre sdk php](https://github.com/adjemin/adjeminpay-php-sdk/) dans votre code PHP. Celui-ci vous permettra d'écouter et d'être notifié lors de vos transactions

## Etape 2 : Interface/Formulaire de paiement

Il vous faudra intégrer le lien du SDK javascript et de JQuery

Dans le head de votre page html ajoutez:

```html
    <script src="https://www.adjeminpay.com/release/seamless/latest/adjeminpay.min.js" type="text/javascript"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>

```

### Information sur la transaction AdjeminPay

Pour faire une transaction avec AdjeminPay vous devez definir les champs suivant :

* `amount`      : Montant du paiement
* `currency`    : Devise du paiement, en CFA
* `transaction_id` : Référence de la transaction
* `designation` : Designation du paiement
* `notify_url`  : uri de notification ou vous recevrez les informations après le paiement

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

    <input type="hidden" id="transaction_id" value="">

    <input type="hidden" id="custom_field" value="">

    <input type="hidden" id="designation" value="Tee-shirt Arafat personnalisé">

    <button type="submit" id="payButton">Payer avec AdjeminPay</button>
</form>
```

NB : _Veillez à enregistrer au préalable dans votre base de donnée les informations concernant une transaction pour pouvoir faire une comparaison plus tard_

#### Lier le formulaire au SDK Javascript

Cliquez sur "Payer avec AdjeminPay" pour commencer, nous ferons ensuite en background un enregistrement en prenant les différents champs puis nous vous notifierons sur l'url de notification.

L'exemple suivant vous montre comment initialiser et lancer le paiement :

```html
<script>
    var AdjeminPay = AdjeminPay();

    AdjeminPay.init({
        apikey: 'VOTRE_API_KEY',
        application_id: 'VOTRE_APPLICATION_ID',
        notify_url: 'VOTRE_URL_DE_NOTIFICATION'
    });
    // Ecouter le feedback de l'initialisation
    AdjeminPay.on('init', function(e){
        // retourne une erreur au cas où votre API_KEY ou APPLICATION_ID est incorrecte
        console.log(e);
    });
    // Ecouter le feedback sur les errerurs
    AdjeminPay.on('error', function(e){
        console.log(e);
    });
    // Lancer la procédure de paiement
    $('payButton').click(function () {
        AdjeminPay.preparePayment({
            amount: parseInt($('#amount').val()),
            transaction_id: $('#transaction_id').val(),
            currency: $('#currency').val(),
            designation: $('#designation').val(),
            custom: $('#custom').val()
        });
        AdjeminPay.renderPaymentView();
    });
</script>
```

## Etape 3 : Ecouter les evenements qui se produisent lors de notre transaction

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
   
   AdjeminPay.on('paymentSuccessfull', function (info) {
        if(paymentInfo.adp_result == 'SUCCESS'){
            $('#result').html(`Votre paiement a été validé avec succès : <br> Montant payé : ${info.adp_amount+}<br>`)
        }else{
            $('#result').html(`Une erreur est survenue : ${info.adp_error_message}`)
        }
   })
```

NB: _ce code est la suite du précédent code JS._
