{* Fahreigenschaften: Werte kommen aus Bootstrap::assignSnowboardSpecs() (Funktionsattribute mit Vater-Fallback) *}
{if !empty($adpSpecsCharacteristics)}
<div class="pentarow text-center adp-specs adp-specs--characteristics">
    <script src="{$adpFrontendURL}js/ecm_polygon_svg.js"></script>

    <div class="col-lg-8 col-lg-push-2 col-md-6 col-md-push-3 col-xs-10 col-xs-push-1">
        <h3 class="adp-specs__heading">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_specs_heading_characteristics')}</h3>
        <center><div id="ecm-attributes-svg"></div></center>
        <center><p class="ecm-attributes-description">-</p></center>
    </div>

    <script>
            $(function () {
                // [label, description, value, max_value]
                let data = [{foreach $adpSpecsCharacteristics as $c}['{$c.label|escape:'javascript'}', '', {$c.value}, {$c.max}]{if !$c@last}, {/if}{/foreach}];
                let ecm_svg = new ECM_POLYGON_SVG(500, 400, 'black', 'red', 5, data);
                $("#ecm-attributes-svg").append(ecm_svg.getHTML());

                $( ".ecm_button" ).on( "mouseenter", function() {
                    let obj = JSON.parse($( this ).attr('attr-ecm-svg'));
                    $(".ecm-attributes-description").html(obj.title + ': ' + obj.value + '/' + obj.max_value);
                });
            }

            );
    </script>
    <style>
        .ecm_button text {
            fill: black;
            font-weight: bolder;
            font-size: 12px;
        }
        .ecm_buttons .ecm_button path {
            opacity : 0;
        }

        .ecm_buttons .ecm_button:hover path.ecm_button_vis {
            opacity : 0.1;
        }
    </style>
</div>
{/if}

{* Körpergewicht und Fahrlevel: Werte kommen aus Bootstrap::assignDetailExtras() *}
{if !empty($adpWeight)}
    {assign var=adpWeightSteps value=($isMobile) ? $adpWeight.mobile : $adpWeight.desktop}
    <p class="ecm-gewicht-title">{if $lang eq "eng"}Suggested Weight:{else}Empfohlenes Körpergewicht:{/if}</p>
    <div class="ecm-gewicht-list" data-toggle="tooltip" data-placement="bottom" data-html="true"
         title="{if $lang eq "eng"}Suggested Weight:{else}Empfohlenes Körpergewicht:{/if}<br>{$adpWeight.from} - {$adpWeight.to} kg">
        {foreach $adpWeightSteps as $step}
            <div class="ecm-gewicht-item{if $step.set} set{/if}" style="width: {100 / ($adpWeightSteps|count)}%;">{$step.label}</div>
        {/foreach}
    </div>
{/if}

{if !empty($adpLevel)}
    <p class="ecm-gewicht-title">{if $lang eq "eng"}Rider Skills:{else}Fahrlevel:{/if}</p>
    <div class="ecm-gewicht-list" data-toggle="tooltip" data-placement="bottom" data-html="true"
         title="{if $lang eq "eng"}Rider Skills:{else}Fahrlevel:{/if}<br>{if $adpLevel.from !== $adpLevel.to}{$adpLevel.from} - {$adpLevel.to}{else}{$adpLevel.from}{/if}">
        {foreach $adpLevel.steps as $step}
            <div class="ecm-gewicht-item{if $step.set} set{/if}" style="width: {100 / ($adpLevel.steps|count)}%;">{$step.label}</div>
        {/foreach}
    </div>
{/if}
