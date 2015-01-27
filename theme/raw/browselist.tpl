<div id="gallery">
{foreach $items.data.photos post}
        <div class="gall-cell photo">
			{if $post.image.id eq "0"}
				<a class="gall-cell celllink photo" href="{$post.page.url}" style="background-image:url('{$wwwroot}theme/raw/static/images/no_thumbnail.png');background-size: 100%"></a>
			{else}	
				<a class="gall-cell celllink photo" href="{$post.page.url}" style="background-image:url('{$wwwroot}artefact/file/download.php?file={$post.image.id}&view={$post.image.view}&maxwidth=150&minheight=120'); background-color: #EEEEEE; background-repeat: repeat-x;"></a>
			{/if}
            <span class="gall-span photo">
                <a href="{$post.owner.profileurl}">
                    <div class="avatar fl"><img alt="{$post.owner.name}"  src="{$post.owner.avatarurl}" /></div>
                </a>
                <div class="ownername fl cl"><a href="{$post.owner.profileurl}">{$post.owner.name}</a></div>
				{if $post.image.id neq "0"}
					<a href="{$wwwroot}view/artefact.php?artefact={$post.image.id}&view={$post.image.view}">
						<span class="imagelinkicon"></span>
						<span class="imagelink">View image</span>
					</a>
				{/if}
            </span>
            <span class="pagelinkicon"></span>
            <a href="{$post.page.url}"><span class="pagelink">{$post.page.title}</span></a>
        </div>
{/foreach}
</div>
