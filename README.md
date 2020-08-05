
# [AdjeminPay](https://www.adjeminpay.com) Seamless Integration

## adjeminpay-javascript-sdk

Seamless javascript integration of e-payment for websites with AdjeminPay
AdjeminPay Seamless Integration permet d'intégrer facilement AdjeminPay de façon transparente à son service en ligne, c'est à dire que le client effectue le paiement sans quitter le site
du marchand.

## Compatibilité Application Hybride

AdjeminPay Seamless Integration a été testé et fonctionne sur :

* Cordova
* phoneGap
* Ionic
* jQuery Mobile
L'integration de ce SDK se fait en trois etapes :

## Etape 1 : Préparer la page de notification

Pour ceux qui possèdent des services qui ne neccessitent pas un traitement des notifications de paiement de AdjeminPay, vous pouvez passer directement à l'etape 2, par exemple les services de don.

A chaque paiement, AdjeminPay vous notifie via un lien de notification, nous vous conseillons de toujours le traiter côté serveur. Nous allons utiliser PHP dans ce cas de figure :
Script index.php dans http://mondomaine.com/notify/ (le script doit se trouver dans le repertoire de votre url notify_url) ;

```php
<?php
if (isset($_POST['adp_trans_id'])) {
    // SDK PHP de AdjeminPay
    require_once __DIR__ . '/AdjeminPay.php';
    require_once __DIR__ . '/commande.php';

    //La classe commande correspond à votre colonne qui gère les transactions dans votre base de données
    $commande = new Commande();
    try {
        // Initialisation de AdjeminPay et Identification du paiement
        $id_transaction = $_POST['adp_trans_id'];
        $apiKey = _VOTRE_APIKEY_;
        $site_id = _VOTRE_SITEID_;
        $plateform = "TEST"; // Valorisé à PROD si vous êtes en production
        $AdjeminPay = new AdjeminPay($site_id, $apiKey, $plateform);
        // Reprise exacte des bonnes données chez AdjeminPay
        $AdjeminPay->setTransId($id_transaction)->getPayStatus();
        $adp_site_id = $AdjeminPay->_adp_site_id;
        $signature = $AdjeminPay->_signature;
        $adp_amount = $AdjeminPay->_adp_amount;
        $adp_trans_id = $AdjeminPay->_adp_trans_id;
        $adp_custom = $AdjeminPay->_adp_custom;
        $adp_currency = $AdjeminPay->_adp_currency;
        $adp_payid = $AdjeminPay->_adp_payid;
        $adp_payment_date = $AdjeminPay->_adp_payment_date;
        $adp_payment_time = $AdjeminPay->_adp_payment_time;
        $adp_error_message = $AdjeminPay->_adp_error_message;
        $payment_method = $AdjeminPay->_payment_method;
        $adp_phone_prefixe = $AdjeminPay->_adp_phone_prefixe;
        $cel_phone_num = $AdjeminPay->_cel_phone_num;
        $adp_ipn_ack = $AdjeminPay->_adp_ipn_ack;
        $created_at = $AdjeminPay->_created_at;
        $updated_at = $AdjeminPay->_updated_at;
        $adp_result = $AdjeminPay->_adp_result;
        $adp_trans_status = $AdjeminPay->_adp_trans_status;
        $adp_designation = $AdjeminPay->_adp_designation;
        $buyer_name = $AdjeminPay->_buyer_name;

        // Recuperation de la ligne de la transaction dans votre base de données
        $commande->setTransId($id_transaction);
        $commande->getCommandeByTransId();
        // Verification de l'etat du traitement de la commande
        if ($commande->getStatut() == '00') {
            // La commande a été déjà traité
            // Arret du script
            die();
        }
        // Dans le cas contrait, on remplit notre ligne des nouvelles données acquise en cas de tentative de paiement sur AdjeminPay
        $commande->setMethode($payment_method);
        $commande->setPayId($adp_payid);
        $commande->setBuyerName($buyer_name);
        $commande->setSignature($signature);
        $commande->setPhone($cel_phone_num);
        $commande->setDatePaiement($adp_payment_date . ' ' . $adp_payment_time);

        // On verifie que le montant payé chez AdjeminPay correspond à notre montant en base de données pour cette transaction
        if ($commande->getMontant() == $adp_amount) {
            // C'est OK : On continue le remplissage des nouvelles données
            $commande->setErrorMessage($adp_error_message);
            $commande->setStatut($adp_result);
            $commande->setTransStatus($adp_trans_status);
            if($adp_result == '00'){
                //Le paiement est bon
                // Traitez et delivrez le service au client
            }else{
                //Le paiement a échoué
            }
        } else {
            //Fraude : montant payé ' . $adp_amount . ' ne correspond pas au montant de la commande
            $commande->setStatut('-1');
            $commande->setTransStatus('REFUSED');
        }
        // On met à jour notre ligne
        $commande->update();
    } catch (Exception $e) {
        echo "Erreur :" . $e->getMessage();
        // Une erreur s'est produite
    }
} else {
    // Tentative d'accès direct au lien IPN
}
?>
```

## Etape 2 : Préparation du formulaire de paiement

Avant de commencer cette etape, il faut lier le seamless SDK à votre page :

* `https://www.adjeminpay.com/cdn/seamless_sdk/latest/adjeminpay.prod.min.js`    : si vous êtes en production

Cela se fait dans la balise head de votre page web

Exemple (en PROD) :

```html
   <head>
       ...
       <script charset="utf-8"
               src="https://www.adjeminpay.com/cdn/seamless_sdk/latest/adjeminpay.prod.min.js"
               type="text/javascript">
       </script>
   </head>
```

### Creation du formulaire AdjeminPay

Le formulaire de paiement AdjeminPay est constitué de :

* `amount`      : Montant du paiement
* `currency`    : Devise du paiement, toujours en CFA pour le moment
* `trans_id`    : L'identifiant de la transaction, elle est unique
* `designation` : La designation de votre paiement
* `notify_url`  : le lien de notification silencieuse (IPN) après paiement

Vous pouvez ajouter en option ces deux elements :

* `cel_phone_num`      : Numéro de téléphone sur lequel l'utilisateur effectuera le paiement
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
    AdjeminPay.setConfig({
            apikey: '174323661757617531bf99c9.80613927',
            site_id: 393509,
            notify_url: 'http://mondomaine.com/notify/'
        });
    var process_payment = document.getElementById('process_payment');
        process_payment.addEventListener('click', function () {
            AdjeminPay.setSignatureData({
                amount: parseInt(document.getElementById('amount').value),
                trans_id: document.getElementById('trans_id').value,
                currency: document.getElementById('currency').value,
                designation: document.getElementById('designation').value,
                custom: document.getElementById('adp_custom').value
            });
            AdjeminPay.getSignature();
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
        if(typeof paymentInfo.lastTime != 'undefined'){
            result_div.innerHTML = '';
            if(paymentInfo.adp_result == '00'){
                result_div.innerHTML = 'Votre paiement a été validé avec succès : <br> Montant payé :'+paymentInfo.adp_amount+'<br>';
            }else{
                result_div.innerHTML = 'Une erreur est survenue :'+paymentInfo.adp_error_message;
            }
        }
   });
</script>
```

## Compatibilité Navigateurs Web

AdjeminPay Seamless Integration a été testé et fonctionne sur tous les navigateurs modernes y compris :

* Chrome
<!-- * Safari -->
* Firefox
* Opera
* Internet Explorer 8+.

## Votre Api Key et Site ID

Ces informations sont disponibles dans votre BackOffice AdjeminPay.

## Exemple Intégration

Vous trouverez un exemple d'intégration complet dans le dossier exemple/html/
