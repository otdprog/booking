$(document).ready(function() {
    var $gallery = $('.portfolio-item').isotope({
        itemSelector: '.item',
        layoutMode: 'masonry',
        percentPosition: true,
        masonry: {
            columnWidth: '.item', // Визначаємо ширину колонки
            gutter: 0 // Відключаємо відступи
        }
    });

    function initMagnificPopup(filterValue) {
        $('.popup-btn').off('click');

        let visiblePhotos = (filterValue === '*') ? $('.popup-btn') : $('.portfolio-item ' + filterValue + ' .popup-btn');

        visiblePhotos.magnificPopup({
            type: 'image',
            gallery: {
                enabled: true
            }
        });
    }

    initMagnificPopup('*');

    $('.portfolio-menu ul li').click(function() {
        $('.portfolio-menu ul li').removeClass('active');
        $(this).addClass('active');

        let filterValue = $(this).attr('data-filter');
        $gallery.isotope({ filter: filterValue });

        setTimeout(function () {
            $gallery.isotope('layout');
        }, 300);

        initMagnificPopup(filterValue);
    });

    $gallery.imagesLoaded().progress(function () {
        $gallery.isotope('layout');
    });
});