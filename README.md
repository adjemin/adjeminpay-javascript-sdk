# [AdjeminPay](https://www.adjeminpay.net) Seamless Integration

## AdjeminPay-javascript-sdk

Le seamless javascript vous permet d'intégrer le paiement en ligne via mobile money dans votre site

L'intégration de ce SDK se fait en trois étapes :

## Etape 1 : Avoir un compte et une application sur AdjeminPay

Avant d'intégrer le seamless vous devez d'abord vous [inscrire et créer une application sur AdjeminPay](https://merchant.adjeminpay.net/customer/register/).

## Etape 2 : Page de paiement

La page de paiement est la page où vous envoyez les clients de votre site pour finaliser leur commande
A cette étape vous devez avoir déjà généré une reference pour la transaction

Ajouter le lien du sdk et de jquery:

```html

    <script src="https://api.adjeminpay.net/release/seamless/latest/adjeminpay.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>

```

### Etape 2.1 : Informations sur votre transaction

Pour faire une transaction avec AdjeminPay vous devez definir les champs suivant :

* `amount`      : Montant du paiement
* `currency`    : Devise du paiement
* `transaction_id` : Référence de la transaction
* `designation` : Designation du paiement
* `notify_url`  : uri de notification ou vous recevrez les informations après le paiement

Exemple :

```html

<div id="result">
    <h1 id="result-title"></h1>
    <p id="result-message"></p>
    <p id="result-status"></p>
</div>
<form id="paiement">
    <input type="hidden"  id="amount" value="25000">
    <input type="hidden" id="currency" value="CFA">
    <input type="hidden"  id="adp_signature" >
    <input type="hidden" id="transaction_id" value="nkHgzAivULfVzvvEkbMsEI7ZjOlRxl9G">
    <input type="hidden" id="designation" value="Paire de basket Air Force">
    <button type="submit" id="payBtn">Payer avec AdjeminPay</button>
</form>

```

NB : _Veuillez générer votre transaction_id dynamiquement et enregistré votre transaction dans votre base de donnée_

#### Etape 2.2 : Lier le formulaire au SDK Javascript

Cliquez sur "Payer" pour commencer, le paiement sera préparé par AdjeminPay et la page de paiement sera générée et affichée.

L'exemple suivant vous montre comment initialiser et lancer le paiement :

```html
<script>
    var AdjeminPay = AdjeminPay();

    AdjeminPay.on('init', function (e) {
        // retourne une erreur au cas où votre CLIENT_ID ou CLIENT_SECRET est incorrecte
        signature = e;
        $("#adp_signature").val(signature);
    });

    // Lance une requete ajax pour vérifier votre API_KEY et APPLICATION_ID et initie le paiement
    AdjeminPay.init({
        client_id : 'VOTRE_CLIENT_ID',
        client_secret : "VOTRE_CLIENT_SECRET",
        transaction_id : $('#transaction_id').val(),
        designation :  $('#designation').val(),
        amount :  parseInt($('#amount').val()),
    });

    // Ecoute le feedback sur les erreurs
    AdjeminPay.on('error', function (e) {
        // la fonction que vous définirez ici sera exécutée en cas d'erreur
        console.log(e);
        $("#result-title").html(e.title);
        $("#result-message").html(e.message);
        $("#result-status").html(e.status);
    });

    // Lancer la procédure de paiement au click
    $('#payBtn').on('click', function () {

        // Vérifie vos informations et prépare le paiement
        // S'il y a une erreur à cette étape, AdjeminPay.on('error') sera exécuté

        AdjeminPay.preparePayment({
            amount: parseInt($('#amount').val()),
            transaction_id: $('#transaction_id').val(),
            currency: $('#currency').val(),
            designation: $('#designation').val(),
            custom: $('#custom_field').val(),
            notify_url: 'https://webhook.site/427ed2b8-db3e-4ac6-be64-9ecb5b68e420',
            signature : $('#adp_signature').val(),
            return_url :'https://application.example.com/return',
            cancel_url : 'https://application.example.com/cancel'
            // le notify_url est TRES IMPORTANT
            // c'est lui qui permettra de notifier votre backend
        });

        // Si l'étape précédante n'a pas d'erreur,
        // cette ligne génère et affiche l'interface de paiement AdjeminPay
        AdjeminPay.renderPaymentView();
    });
</script>
```

## Etape 3 : Réagir aux évènements et exécuter des callbacks lors de l'exécution de votre transaction

Lorsque la page de paiement est générée, AdjeminPay vous permet de suivre toutes les étapes du paiement via des évènements.
Ces évènements retournent des données sous forme d'objet que vous pouvez utiliser dans vos callbacks

* `error` : Une ou plusieurs erreurs se sont produites :
      - soit dans la vérification de vos données de paiement, notamment transaction_id, client_id et client_secret
      - soit dans les requetes sur notre serveur
        Un message d'erreur s'affiche et le paiement est stoppé

```js
    AdjeminPay.on('error', function(errorData)){
        console.log(errorData.error);
        console.log(errorData.message);
    }
```

Les évènements suivants retournent un objet qui contient les informations de la transaction (title, status, message)

* `paymentPending`    : Le paiement est en attente (le client a ouvert l'interface de paiement)
* `paymentTerminated` : Le paiement est terminé, le status est soit validé ou échoué ou annulé
* `paymentSuccessful` : Le paiement est réussi, le client a payé et l'interface de paiement s'est fermée
* `paymentFailed` : Le paiement a échoué :
      - soit le solde du client n'est pas suffisant
      - soit le code otp n'est pas correct
      - soit le client n'a pas confirmé le paiement
      - soit une erreur au niveau de l'opérateur s'est produite
        NB : _ces détails sont disponiblent dans le callback des evenements_
* `paymentCancelled` : Le paiement a été annulé : le client a cliqué sur le bouton 'Annuler'

Exemple :

```js
    // Payment en attente
    AdjeminPay.on('paymentPending', function (e) {
        console.log('<<<<<<< pending !');
        console.log('>>>>>>> Paiement en attente !');
        console.log(e.title);
        console.log(e.status);
        console.log(e.message);
        // ATTENDRE
    });
    // Payment terminé
    AdjeminPay.on('paymentTerminated', function (e) {
        console.log('<<<<<<< terminated !');
        console.log('>>>>>>> Paiement terminé !');
        console.log(e.title);
        console.log(e.status);
        console.log(e.message);
        // EXECUTER UN CALLBACK
    });
    // Payment réussi
    AdjeminPay.on('paymentSuccessful', function (e) {
        console.log('<<<<<<< Successful !');
        console.log('>>>>>>> Paiement réussi !');
        console.log(e.title);
        console.log(e.status);
        console.log(e.message);
        // ACTION redirection, popup de félicitation etc
    });
    // Payment échoué
    AdjeminPay.on('paymentFailed', function (e) {
        console.log('<<<<<<< Echec !');
        console.log('>>>>>>> Paiement echoué !');
                console.log(e.message)
        // ACTION redirection etc
    });

    // Payment annulé
    AdjeminPay.on('paymentCancelled', function (e) {

        console.log('<<<<<<< Echec !');
        console.log('>>>>>>> Paiement annulé !');
        console.log(e.title);
        console.log(e.status);
        console.log(e.message);
        // ACTION
    });
    
```

## Etape 4 : Capter la notification de paiement dans votre backend

Vous souvenez-vous de ```notify_url: 'VOTRE_URL_DE_NOTIFICATION'``` ?
Vous devez maintenant l'implémenter dans votre backend pour être notifié de l'évolution du paiement et mettre à jour votre base de données

AdjeminPay envoie une requête POST à VOTRE_URL_DE_NOTIFICATION avec les données suivantes : (transaction_id, status, data)

* `transaction_id`    : L'id de transaction que vous avez défini à l'étape 2.1
* `status` : Le status du paiement
* `data` : La transaction AdjeminPay complete, avec tous les détails

Un exemple complet se trouve dans /exemple/index.html

Une page prete à l'emploi se trouve dans /exemple/adjeminpay.html
