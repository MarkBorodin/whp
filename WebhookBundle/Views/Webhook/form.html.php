<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Mautic\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MauticPlugin\WHPBundle\Integration\WHPIntegration;

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'mauticWebhook');

$header = ($entity->getId()) ?
    $view['translator']->trans('mautic.webhook.webhook.header.edit',
        ['%name%' => $view['translator']->trans($entity->getName())]) :
    $view['translator']->trans('mautic.webhook.webhook.header.new');

$view['slots']->set('headerTitle', $header);

?>

<?php echo $view['form']->start($form); ?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- container -->
    <div class="col-md-9 bg-auto height-auto">
        <div class="pa-md">
            <div class="row">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['name']); ?>
                    <?php echo $view['form']->row($form['description']); ?>
                    <?php echo $view['form']->row($form['secret']); ?>
                    <?php echo $view['form']->row($form['webhookUrl']); ?>

                    <!--                    CUSTOM-->
                    <?php
                    $isPremium = $GLOBALS['isPremium'];
                    if($isPremium){
                        echo $view['form']->row($form['extra']).PHP_EOL;
                        echo $view['form']->row($form['subject']).PHP_EOL;
                        echo $view['form']->row($form['method']).PHP_EOL;
                        echo $view['form']->row($form['headers']).PHP_EOL;
                        echo '<button type="button" class="btn btn-success" onclick="addFormToCollectionHeader(this)" data-collection-holder-class="receivedPairs">'.$view['translator']->trans('mautic.webhook.form.add.header').'</button>'.'<br>'.'<br>'.'<br>';
                        echo $view['form']->row($form['authType']).PHP_EOL;
                        echo $view['form']->row($form['login']).PHP_EOL;
                        echo $view['form']->row($form['password']).PHP_EOL;
                        echo $view['form']->row($form['token']).PHP_EOL;
//                        echo $view['form']->row($form['actualLoad']).PHP_EOL;
                        echo $view['form']->row($form['testContactId']).PHP_EOL;
//                        echo $view['form']->row($form['fieldsWithValues']).PHP_EOL;
                        echo $view['form']->row($form['receivedPairs']).PHP_EOL;
//                        echo $view['form']->row($form['addField']).'<br>'.'<br>';

                        echo '<button type="button" class="btn btn-success" onclick="addFormToCollection(this)" data-collection-holder-class="receivedPairs">'.$view['translator']->trans('mautic.webhook.form.add.received.pair').'</button>'.'<br>'.'<br>';

                    }
                    ?>
                    <!--                    CUSTOM-->

                    <div class="row">
                        <div class="col-md-5">
                            <?php echo $view['form']->row($form['sendTest']); ?>
                        </div>

<!--                        CUSTOM-->
                        <div class="col-md-5">
                            <?php
                            $isPremium = $GLOBALS['isPremium'];
                            if($isPremium) {
                                echo $view['form']->row($form['sendTestPrem']);
                            }
                            ?>
                        </div>
<!--                        CUSTOM-->

                        <div class="col-md-2">
                            <span id="spinner" class="fa fa-spinner fa-spin hide"></span>
                        </div>
                        <div class="col-md-5">
                            <div id="tester" class="text-right"></div>
                        </div>
                    </div>

                </div>


                <?php
                $isPremium = $GLOBALS['isPremium'];
                if($isPremium) {
                    echo '<div class="col-md-6" id="lead-fields">';
                        echo '<p><b>'.$view['translator']->trans('mautic.webhook.form.available.contact.fields').'</b><p>';
                        foreach ($GLOBALS['leadFields'] as $field) {
                            echo $field . PHP_EOL . '<br>';
                        }
                    echo '</div>';

                    echo '<div class="col-md-6" id="company-fields">';
                        echo '<p><b>'.$view['translator']->trans('mautic.webhook.form.available.company.fields').'</b><p>';
                        foreach ($GLOBALS['companyFields'] as $field) {
                            echo $field . PHP_EOL . '<br>';
                        }
                    echo '</div>';
                }
                ?>

            </div>
        </div>
    </div>
    <div class="col-md-3 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php echo $view['form']->row($form['category']); ?>
            <?php echo $view['form']->row($form['eventsOrderbyDir']); ?>
            <?php echo $view['form']->row($form['isPublished']); ?>
        </div>
        <div class="col-md-6" id="event-types">
            <?php echo $view['form']->row($form['events']); ?>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>




<script>

    // received pairs

    const addReceivedPairsFormDeleteLink = (receivedPair) => {
        const removeFormButton = document.createElement('button')
        removeFormButton.classList
        removeFormButton.className = 'btn btn-danger'
        removeFormButton.innerText = 'Delete'

        receivedPair.append(removeFormButton);
        var br = document.createElement("br");
        receivedPair.appendChild(br);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault()
            receivedPair.parentNode.parentNode.remove();
        });
    }

    const receivedPairs = document.querySelectorAll(`div[id^="webhook_receivedPairs_"]`)
    receivedPairs.forEach((receivedPair) => {
        addReceivedPairsFormDeleteLink(receivedPair)
    })

    const addFormToCollection = (e) => {
        var items = document.querySelectorAll(`div[id^="webhook_receivedPairs_"]`);
        var num = Number(items.length)
        const collectionHolder = document.querySelector(`div[id^="webhook_receivedPairs"]`);
        const item = document.createElement('div');
        item.innerHTML = collectionHolder
            .dataset
            .prototype
            .replace(
                '__name__label__',
                num + 1
            )
            .replace(
                /autocomplete="false"/g,
                'autocomplete="false" class="form-control"'
            )
            .replace(
                /__name__/g,
                num
            );
        addReceivedPairsFormDeleteLink(item);
        collectionHolder.appendChild(item);
        var br = document.createElement("br");
        collectionHolder.appendChild(br);
        collectionHolder.dataset.index = num;
    };

    // received pairs


    // headers

    const addHeadersFormDeleteLink = (header) => {
        const removeFormButton = document.createElement('button')
        removeFormButton.classList
        removeFormButton.className = 'btn btn-danger'
        removeFormButton.innerText = 'Delete'

        header.append(removeFormButton);
        var br = document.createElement("br");
        header.appendChild(br);

        removeFormButton.addEventListener('click', (e) => {
            e.preventDefault()
            header.parentNode.parentNode.remove();
        });
    }

    const headers = document.querySelectorAll(`div[id^="webhook_headers_"]`)
    headers.forEach((header) => {
        addHeadersFormDeleteLink(header)
    })

    const addFormToCollectionHeader = (e) => {
        var items = document.querySelectorAll(`div[id^="webhook_headers_"]`);
        var num = Number(items.length)
        const collectionHolder = document.querySelector(`div[id^="webhook_headers"]`);
        const item = document.createElement('div');
        item.innerHTML = collectionHolder
            .dataset
            .prototype
            .replace(
                '__name__label__',
                num + 1
            )
            .replace(
                /autocomplete="false"/g,
                'autocomplete="false" class="form-control"'
            )
            .replace(
                /__name__/g,
                num
            )
        ;
        addHeadersFormDeleteLink(item);
        collectionHolder.appendChild(item);
        var br = document.createElement("br");
        collectionHolder.appendChild(br);
        collectionHolder.dataset.index = num;
    };

    // headers

</script>



