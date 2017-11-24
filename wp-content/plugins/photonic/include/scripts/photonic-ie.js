jQuery(document).ready(function ($) {
	$('.photonic-masonry-layout').each(function (idx, grid) {
		var minWidth = isNaN(Photonic_JS.masonry_min_width) ? 200 : Photonic_JS.masonry_min_width;
		minWidth = parseInt(minWidth)
		$(grid).waitForImages(function () {
			$(this).masonry({
				itemSelector: '.photonic-level-1,.photonic-level-2',
				columnWidth: minWidth
			});
		});
	});
});
