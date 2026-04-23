{block name='productdetails-cheaper'}
    {if $smarty.get.adp_cheaper eq 'success' && $smarty.get.adp_ka == $Artikel->kArtikel}

        <div class="alert alert-success" role="alert">
            <strong>Vielen Dank!</strong> Wir haben Ihren Preishinweis erhalten und werden ihn prüfen.
        </div>
        <script>
            $(document).ready(function () {
                $('#cheaper-{$Artikel->kArtikel|intval}').modal('show');
            });
        </script>

    {else}

        {if $smarty.get.adp_cheaper eq 'error' && $smarty.get.adp_ka == $Artikel->kArtikel}
            <div class="alert alert-danger" role="alert">
                {if $smarty.get.adp_err eq 'validation'}
                    Bitte füllen Sie E-Mail-Adresse und Link korrekt aus.
                {elseif $smarty.get.adp_err eq 'csrf'}
                    Ungültige Anfrage. Bitte laden Sie die Seite neu und versuchen Sie es erneut.
                {else}
                    Beim Senden ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.
                {/if}
            </div>
            <script>
                $(document).ready(function () {
                    $('#cheaper-{$Artikel->kArtikel|intval}').modal('show');
                });
            </script>
        {/if}

        {block name='productdetails-question-on-item-form'}
            {form method="post" action="" class="jtl-validate" addhoneypot=true}
                {input type="hidden" name="adp_cheaper_submit" value="1"}
                {input type="hidden" name="adp_artikel_id"   value=$Artikel->kArtikel}
                {input type="hidden" name="adp_artikel_name" value=$Artikel->cName}

                {formgroup label-for="adp_email_{$Artikel->kArtikel}" label="Ihre E-Mail-Adresse *"}
                    {input type="email"
                           name="adp_email"
                           id="adp_email_{$Artikel->kArtikel}"
                           required=true
                           placeholder=" "}
                {/formgroup}

                {formgroup label-for="adp_url_{$Artikel->kArtikel}" label="Link zum günstigeren Angebot *"}
                    {input type="url"
                           name="adp_url"
                           id="adp_url_{$Artikel->kArtikel}"
                           required=true
                           placeholder="https://"}
                {/formgroup}

                {formgroup label-for="adp_nachricht_{$Artikel->kArtikel}" label="Nachricht (optional)"}
                    {textarea name="adp_nachricht"
                              id="adp_nachricht_{$Artikel->kArtikel}"
                              rows="3"
                              placeholder=" "}{/textarea}
                {/formgroup}

                {row}
                    {col md="auto" class="ml-auto-util"}
                        {button type="submit" variant="primary" block=true}
                            {lang key='sendQuestion' section='productDetails'}
                        {/button}
                    {/col}
                {/row}
            {/form}
        {/block}

    {/if}
{/block}
