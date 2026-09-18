{block name='productdetails-cheaper'}
    {assign "l" $oPlugin_artikel_details_plus->getLocalization()}
    {assign var=adpModalId value=($Artikel->kArtikelVariKombi > 0) ? $Artikel->kArtikelVariKombi : $Artikel->kArtikel}
    {assign var=adpState value=(isset($smarty.get.adp_cheaper) && isset($smarty.get.adp_ka) && $smarty.get.adp_ka == $Artikel->kArtikel) ? $smarty.get.adp_cheaper : ''}
    {assign var=adpErr value=(isset($smarty.get.adp_err)) ? $smarty.get.adp_err : ''}

    {if $adpState === 'success'}

        <div class="alert alert-success" role="alert">
            {$l->getTranslation('artikel_details_plus_cheaper_success')}
        </div>
        <script>
            $(document).ready(function () {
                $('#cheaper-{$adpModalId|intval}').modal('show');
            });
        </script>

    {else}

        {if $adpState === 'error'}
            <div class="alert alert-danger" role="alert">
                {if $adpErr === 'validation'}
                    {$l->getTranslation('artikel_details_plus_cheaper_err_validation')}
                {elseif $adpErr === 'csrf'}
                    {$l->getTranslation('artikel_details_plus_cheaper_err_csrf')}
                {else}
                    {$l->getTranslation('artikel_details_plus_cheaper_err_general')}
                {/if}
            </div>
            <script>
                $(document).ready(function () {
                    $('#cheaper-{$adpModalId|intval}').modal('show');
                });
            </script>
        {/if}

        {block name='productdetails-question-on-item-form'}
            {form method="post" action="" class="jtl-validate" addhoneypot=true}
                {input type="hidden" name="adp_cheaper_submit" value="1"}
                {input type="hidden" name="adp_artikel_id" value=$Artikel->kArtikel}

                {formgroup label-for="adp_email_{$Artikel->kArtikel}"
                           label=$l->getTranslation('artikel_details_plus_cheaper_label_email')}
                    {input type="email"
                           name="adp_email"
                           id="adp_email_{$Artikel->kArtikel}"
                           required=true
                           placeholder=" "}
                {/formgroup}

                {formgroup label-for="adp_url_{$Artikel->kArtikel}"
                           label=$l->getTranslation('artikel_details_plus_cheaper_label_url')}
                    {input type="url"
                           name="adp_url"
                           id="adp_url_{$Artikel->kArtikel}"
                           required=true
                           placeholder="https://"}
                {/formgroup}

                {formgroup label-for="adp_nachricht_{$Artikel->kArtikel}"
                           label=$l->getTranslation('artikel_details_plus_cheaper_label_message')}
                    {textarea name="adp_nachricht"
                              id="adp_nachricht_{$Artikel->kArtikel}"
                              rows="3"
                              placeholder=" "}{/textarea}
                {/formgroup}

                {row}
                    {col md="auto" class="ml-auto-util"}
                        {button type="submit" variant="primary" block=true}
                            {$l->getTranslation('artikel_details_plus_cheaper_submit')}
                        {/button}
                    {/col}
                {/row}
            {/form}
        {/block}

    {/if}
{/block}
