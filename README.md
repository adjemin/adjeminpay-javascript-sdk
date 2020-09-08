# [AdjeminPay](https://www.adjeminpay.net) Seamless Integration

## adjeminpay-javascript-sdk

Le seamless javascript vous permet d'intégrer le paiement en ligne via mobile money dans votre site

L'intégration de ce SDK se fait en trois étapes :

## Etape 1 : Avoir un compte et une application sur AdjeminPay

Avant d'intégrer le seamless vous devez d'abord vous [inscrire et créer une application sur AdjeminPay](https://merchant.adjeminpay.net/customer/register/).

## Etape 2 : Page de paiement

La page de paiement est la page où vous envoyez les clients de votre site pour finaliser leur commande
A cette étape vous devez avoir déjà généré une reference pour la transaction


Ajouter le lien du sdk et de jquery:

```html

    <script src="https://cdn.adjeminpay.net/release/seamless/latest/adjeminpay.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>

```

### Information sur la transaction AdjeminPay

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

    <input type="hidden" id="amount" value="100">
    <input type="hidden" id="currency" value="CFA">

    <!-- NB: La longeur maximum d'un id de transaction est de 191 caractères -->
    <input type="hidden" id="transaction_id"
        value="d3aa42a9-1-1-c48*-4df2-a2f0-2921780ab71d-d3aa42a9-4df26-a2f0-2921780ab71d9">
    <!-- Champ personnalisé où vous pourrez définir des informations supplémentaires à votre transaction  -->
    <!-- le champ custom est facultatif -->
    <input type="hidden" id="custom_field" value="custom text">

    <input type="hidden" id="designation" value="Tee-shirt Arafat personnalisé">

    <button id="payBtn">Payer</button>

```

NB : _Veuillez générer votre transaction id dynamiquement en enregistrer votre transaction dans votre base de donnée_

#### Lier le formulaire au SDK Javascript

Cliquez sur "Payer" pour commencer, le paiement sera préparé par AdjeminPay et la page de paiement sera générée et affichée.

L'exemple suivant vous montre comment initialiser et lancer le paiement :

```html
<script>
    var AdjeminPay = AdjeminPay();

    AdjeminPay.on('init', function (e) {
        // retourne une erreur au cas où votre API_KEY ou APPLICATION_ID est incorrecte
        console.log(e);
    });

    // Lance une requete ajax pour vérifier votre API_KEY et APPLICATION_ID et initie le paiement
    AdjeminPay.init({
        apikey: 'VOTRE_API_KEY',
        application_id: 'VOTRE_APPLICATION_ID',
        notify_url: 'VOTRE_URL_DE_NOTIFICATION'
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
            custom: $('#custom_field').val()
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
      - soit dans la vérification de vos données de paiement, notamment transaction_id, api_key et application_id
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