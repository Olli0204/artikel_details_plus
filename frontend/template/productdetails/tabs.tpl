{if $oPlugin_artikel_details_plus->getConfig()->getValue("artikel_details_plus_merkmalwerte_aktiv")}
{block name='tab-description-media-types' prepend}
    <style>
        .neue_darstellung {
            width: 70% !important;
            
        }

        @media only screen and (max-width: 1200px) {
        .neue_darstellung {
            width: 100%;
            }
        }
        .pentarow {} 

        .pentarow {
            display: flex; 
            align-content: center;
            justify-content: center;
        }

        .ecm-gewicht-title {
            text-align: center;
            margin-bottom: 0;
        }

        .ecm-gewicht-list {
            display: -webkit-flex;
            display: flex;
            border: 1px solid black;
            text-align: center;
            margin-bottom: 10px;
        }

        .ecm-gewicht-item {
            display: inline-block;
            color: black;
        }

        .ecm-gewicht-item.set {
            background-color: black;
            color: white;
        }
    </style>
    <center>
        <div class="neue_darstellung">
            {include file='productdetails/svg_attributes.tpl' tplscope='details'}
        </div>
    </center>
     <center>
        {include file='productdetails/snowboard_values.tpl' tplscope='details'}
    </center>
{/block}


{block name='productdetails-tabs-card-description-content' prepend}
    <style>
        .neue_darstellung {
            width: 70% !important;
            
        }

        @media only screen and (max-width: 1200px) {
        .neue_darstellung {
            width: 100%;
            }
        }
        .pentarow {} 

        .pentarow {
            display: flex; 
            align-content: center;
            justify-content: center;
        }

        .ecm-gewicht-title {
            text-align: center;
            margin-bottom: 0;
        }

        .ecm-gewicht-list {
            display: -webkit-flex;
            display: flex;
            border: 1px solid black;
            text-align: center;
            margin-bottom: 10px;
        }

        .ecm-gewicht-item {
            display: inline-block;
            color: black;
        }

        .ecm-gewicht-item.set {
            background-color: black;
            color: white;
        }
    </style>
    <center>
        <div class="neue_darstellung">
            {include file='productdetails/svg_attributes.tpl' tplscope='details'}
        </div>
    </center>
    <center>
        {include file='productdetails/snowboard_values.tpl' tplscope='details'}
    </center>
{/block}
{/if}