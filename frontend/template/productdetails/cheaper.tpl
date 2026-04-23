{block name='productdetails-cheaper'}
    {if isset($fehlendeAngaben_fragezumprodukt)}
        {$fehlendeAngaben = $fehlendeAngaben_fragezumprodukt}
    {/if}
    {if isset($position) && $position === 'popup'}
        {if count($Artikelhinweise) > 0}
            {block name='productdetails-question-on-item-alert'}
                {alert dismissable=true variant="danger"}
                    {foreach $Artikelhinweise as $Artikelhinweis}
                        {$Artikelhinweis}
                    {/foreach}
                {/alert}
            {/block}
        {/if}
    {/if}
    {block name='productdetails-question-on-item-form'}

    {* {form action="cheaper.php" method="post"}
        {button type="submit" block=true onclick="event.stopPropagation();"}
            Test Button
        {/button}
    {/form} *}

    <form action="cheaper.php">
        <input name="submit" type="submit" value="Senden">
    </form>
    {* {form action="cheaper.php"
        method="post"
        id="article_cheaper"
        class="jtl-validate"
        addhoneypot=true
        slide=true}
            <fieldset>
                <legend>{lang key='contact'}</legend>
                <p>Hier bitte die Kontaktdaten angeben, damit wir dich kontatktieren können.</p>
                {row}
                    {col cols=12}
                        {include file='snippets/form_group_simple.tpl'
                            options=[
                                'email', 'question_email', 'email',
                                {$Anfrage->cMail|default:null}, {lang key='email' section='account data'},
                                true, null, 'email'
                            ]
                        }
                    {/col}
                {/row}
            </fieldset>
        {block name='productdetails-question-on-item-form-fieldset-product-question'}
        <fieldset>
                <legend>Inhalt</legend>
                {formgroup label-for="question" label="Nachricht"}
                    {if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}
                        <div class="form-error-msg" aria-live="assertive" role="alert" aria-atomic="true"><i class="fas fa-exclamation-triangle"></i> {if $fehlendeAngaben_fragezumprodukt.nachricht > 0}{lang key='fillOut'}{/if}</div>
                    {/if}
                    {textarea name="nachricht" id="question" rows="8" required=true placeholder=" " class="{if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}has-error{/if}" aria-invalid="{if isset($fehlendeAngaben_fragezumprodukt.nachricht) && $fehlendeAngaben_fragezumprodukt.nachricht > 0}true{else}false{/if}"}{if isset($Anfrage)}{$Anfrage->cNachricht}{/if}{/textarea}
                {/formgroup}
                {formgroup label-for="url" label="Url zum günstigeren Angebot"}
                    {textarea name="url" id="item_url" rows="1" required=true placeholder=" " class="{if isset($fehlendeAngaben_fragezumprodukt.url) && $fehlendeAngaben_fragezumprodukt.url > 0}has-error{/if}" aria-invalid="{if isset($fehlendeAngaben_fragezumprodukt.url) && $fehlendeAngaben_fragezumprodukt.url > 0}true{else}false{/if}"}{if isset($Anfrage)}{$Anfrage->cCheaperURL}{/if}{/textarea}
                {/formgroup}
        </fieldset>
        {/block}
        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
            isset($Einstellungen.artikeldetails.produktfrage_abfragen_captcha) && $Einstellungen.artikeldetails.produktfrage_abfragen_captcha !== 'N' && JTL\Session\Frontend::getCustomer()->getID() === 0}
            {block name='productdetails-question-on-item-form-captcha'}
                {row}
                    {col class="{if !empty($fehlendeAngaben_fragezumprodukt.captcha)}has-error{/if}"}
                        {captchaMarkup getBody=true}
                    {/col}
                {/row}
            {/block}
        {/if}

        {if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P' && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ])}
            {block name='productdetails-question-on-item-form-privacy'}
                <p class="privacy text-muted-util small">
                    {link href=$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL() class="popup"}
                        {lang key='privacyNotice'}
                    {/link}
                </p>
            {/block}
        {/if}

        {block name='productdetails-question-on-item-form-submit'}
            {input type="hidden" name="a" value=$Artikel->kArtikel}
            {input type="hidden" name="show" value="1"}
            {input type="hidden" name="fragezumprodukt" value="1"}
            {row}
                {col md='auto' class="ml-auto-util"}
                    {button type="submit" value="1" variant="primary" block=true}
                        {lang key='sendQuestion' section='productDetails'}
                    {/button}
                {/col}
            {/row}
        {/block}
    {/form} *}
    {/block}
{/block}
