{* 20150723 JW added a check to see if jquery is loaded, if it's not loaded then load it http://jquery-howto.blogspot.co.nz/2009/03/check-if-jqueryjs-is-loaded.html *}
<script type="text/javascript">
	if(typeof jQuery.ui == 'undefined') {  
		var jq = document.createElement('script'); jq.type = 'text/javascript';
		jq.src = '{$JQUERYPATH}';
		document.getElementsByTagName('head')[0].appendChild(jq);
	}
</script>
{* inserted the javascript and stylesheet and inline javascript from ./theme/raw/templates/header/head.tpl *}
{foreach from=$JAVASCRIPT item=script}
    <script type="text/javascript" src="{$script}"></script>
{/foreach}

{foreach from=$STYLESHEETLIST item=cssurl}
    <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}

{if isset($INLINEJAVASCRIPT)}
    <script type="text/javascript">
		{$INLINEJAVASCRIPT|safe}
    </script>
{/if}
{* --- *}

{* copied from http://mikekelly.myblog.arts.ac.uk/2014/04/10/browse-pages-and-browse-profile-pages-plugins-for-mahara/ *}
{* removed other code from ./theme/raw/index.tpl that were not needed for this project *}
{$items.pagination|safe}
<div id="browselist" class="fullwidth listing clearfix">
	{$items.tablerows|safe}
</div>
{* --- *}