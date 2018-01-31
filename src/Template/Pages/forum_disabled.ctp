<style type="text/css">
	@import url(http://fonts.googleapis.com/css?family=Give+You+Glory);
	body > div {
		min-height: inherit; height: 100%;
	}
	body {
		position:relative;
		min-height: 100%;
	}
	.outer {
		width: 320px;
		margin: 0 auto;
		text-align: center;
		display:table;
		height: 80%;
		min-height: 500px;
	}
	.inner{
		display: table-cell;
		vertical-align: middle;
	}
	h1.header {
		margin-bottom: 60px;
		font-size: 60px;
		font-family: sans-serif;
	}
	.rotate {
		-webkit-transform:matrix(0.99,-0.11,0.11,0.99,0,0);
		-webkit-transform-origin:center;
		-o-transform:matrix(0.99,-0.11,0.11,0.99,0,0);
		-o-transform-origin:center;
		-ms-transform:matrix(0.99,-0.11,0.11,0.99,0,0);
		-ms-transform-origin:center;
		transform:matrix(0.99,-0.11,0.11,0.99,0,0);
		transform-origin:center;
	}
	.sticky {
		position: relative;
    margin: 0 auto;
    padding: 8px 24px;
		display: table-cell;
		vertical-align: middle;
    width: 240px;
		height: 220px;
		font-family: 'Give You Glory', cursive;
    font-size: 30px;

		padding: 40px;

		background-image:-o-linear-gradient(170deg,rgb(254,253,201) 0%,rgb(247,243,128) 100%);
		background-image:-ms-linear-gradient(170deg,rgb(254,253,201) 0%,rgb(247,243,128) 100%);
		background-image:linear-gradient(170deg,rgb(254,253,201) 0%,rgb(247,243,128) 100%);
		box-shadow:0px 2px 4px 0px rgba(88,88,43,0.63);
		-ms-filter:"progid:DXImageTransform.Microsoft.dropshadow(OffX = 0,OffY = 2,Color = #a158582b,Positive = true)";
		filter:progid:DXImageTransform.Microsoft.dropshadow(OffX = 0,OffY = 2,Color = #a158582b,Positive = true);
	}

	.sticky:before, .sticky:after
	{
		position: absolute;
		width: 40%;
		height: 10px;
		content: ' ';
		left: 4px;
		bottom: 4px;
		background: transparent;
		-ms-transform: skew(-5deg) rotate(-5deg);
		-o-transform: skew(-5deg) rotate(-5deg);
		transform: skew(-5deg) rotate(-5deg);
		box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
		z-index: -1;
	}
	.sticky:after
	{
		left: auto;
		right: 4px;
		-ms-transform: skew(5deg) rotate(5deg);
		-o-transform: skew(5deg) rotate(5deg);
		transform: skew(5deg) rotate(5deg);
	}

	.sticky p {
		margin: 0;
		padding: 0;
	}

</style>
<div class="outer">
	<div class="inner">
		<h1 class="header">
			<?php echo Configure::read('Saito.Settings.forum_name'); ?>
        </h1>
        <div class="rotate">
            <div class="sticky">
                <p>
                    <?php echo Configure::read('Saito.Settings.forum_disabled_text'); ?>
                </p>
            </div>
        </div>
        <div style="margin-top: 50px;">
            <p >
                <!-- Footer -->
            </p>
        </div>
    </div>
</div>