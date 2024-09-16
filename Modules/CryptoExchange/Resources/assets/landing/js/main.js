"use strict";
$(".c-text").on("click", function () {
  $(".active-round").removeClass("active-circle-bg").find("span").css("color", "#635BFF");
  $(this).closest(".c-text-parent").find(".active-round").addClass("active-circle-bg").find("span").css("color", "white")
});

$(document).ready(function () {
  $(window).scroll(function () {
    var scroll = $(window).scrollTop();
    if (scroll > 100) {
      $(".navbar-fixed-top").css("background", "white");
      $(".navbar-fixed-top").css("transition-duration", ".4s");
      $(".navbar-fixed-top").css("box-shadow", "0px 1px 5px rgba(58, 12, 163, 0.05)");
    }
    else {
      $(".navbar-fixed-top").css("background", "unset");
      $(".navbar-fixed-top").css("box-shadow", "unset");
    }
  });
  $(window).on('load', function () {
    var scroll = $(window).scrollTop();
    if (scroll > 100) {
      $(".navbar-fixed-top").css("background", "white");
      $(".navbar-fixed-top").css("transition-duration", "0.3s");
      $(".navbar-fixed-top").css("box-shadow", "0px 1px 5px rgba(58, 12, 163, 0.05)");
    }
    else {
      $(".navbar-fixed-top").css("background", "unset");
      $(".navbar-fixed-top").css("box-shadow", "unset");
    }
  });

  $('#lang').on('change', function (e)
  {
      e.preventDefault();
      var lang = $(this).val();
      var url = SITE_URL+'/change-lang';
      $.ajax({
        type: 'get',
        url: url,
        data: {lang: lang},
        success: function (msg)
        {
          if (msg == 1)
          {
            location.reload();
          }
        }
      });
  });

})

//   Collapse
var coll = document.getElementsByClassName("collapsible");
var j;
for (j = 0; j < coll.length; j++) {
  coll[j].addEventListener("click", function () {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.maxHeight) {
      content.style.maxHeight = null;
    } else {
      content.style.maxHeight = 200 + "px";
    }
  });
}

// crypto dropdown
$(document).ready(function () {

  $('body').on('click', '.apto-trigger-dropdown', function (e) {

    e.stopPropagation();

    $(this).closest('.apto-dropdown-wrapper').find('.dropdown-menu').toggleClass('show');
  });


  $('body').on('click', '.dropdown-item', function (e) {

    e.stopPropagation();
    let $selectedValue = $(this).val();
    let $icon = $(this).find('svg');
    let $icon2 = $(this).find('span');
    let $btn = $(this).closest('.apto-dropdown-wrapper').find('.apto-trigger-dropdown');
    $(this).closest('.apto-dropdown-wrapper').find('.dropdown-menu').removeClass('show').attr('data-selected', $selectedValue);
    $btn.find('svg').remove();
    $btn.find('span').remove();
    $btn.prepend($icon[0].outerHTML);
    $btn.prepend($icon2[0].outerHTML);

  });

  $(".opens").on("click", function () {
    $("#cls").addClass("d-open");
    $("#cls").removeClass("d-close");
    $("#opn").addClass("d-close");
    $("#opn").removeClass("d-open");
    $("#top-header").removeClass("px-240p");
    $("#navbarSupportedContent").removeClass("d-close");
    $("#top-header").addClass("header-bg");
    $("#d-mobile").removeClass("down-hidden");
    $("#d-mobile").addClass("d-close");
  });
  $(".closed").on("click", function () {
    $("#cls").removeClass("d-open");
    $("#cls").addClass("d-close");
    $("#opn").removeClass("d-close");
    $("#opn").addClass("d-open");
    $("#top-header").addClass("px-240p");
    $("#navbarSupportedContent").addClass("d-close");
    $("#top-header").removeClass("header-bg");
    $("#d-mobile").addClass("down-hidden");
    $("#d-mobile").removeClass("d-close");
    $("#img-mobile-div").css("display", "none");
  });


});

// select option
var langArray = [];
$('.vodiapicker option').each(function () {
  var img = $(this).attr("data-thumbnail");
  var text = this.innerText;
  var value = $(this).val();
  var item = '<li><img src="' + img + '" alt="" value="' + value + '"/><span>' + text + '</span></li>';
  langArray.push(item);
})

$('#ul-get-id').html(langArray);
//Set the button value to the first el of the array
$('.btn-select').html(langArray[0]);
$('.btn-select').attr('value', 'en');
//change button stuff on click
$("#ul-get-id li").on("click", function () {
  var img = $(this).find('img').attr("src");
  var value = $(this).find('img').attr('value');
  var text = this.innerText;
  var item = '<li><img src="' + img + '" alt="" /><span>' + text + '</span></li>';
  $('.btn-select').html(item);
  $('.btn-select').attr('value', value);
  $(".ul-get-display").toggle();
});

$(".btn-select").on("click", function () {
  $(".ul-get-display").toggle();
  $(".ul-display").css("display","none");
});


var langArray2 = [];
$('.vodiapicker2 option').each(function () {
  var img2 = $(this).attr("data-thumbnail");
  var text2 = this.innerText;
  var value2 = $(this).val();
  var item2 = '<li><img src="' + img2 + '" alt="" value="' + value2 + '"/><span>' + text2 + '</span></li>';
  langArray2.push(item2);
})  

$('#ul-id-one').html(langArray2);
//Set the button value to the first el of the array
$('.btn-select2').html(langArray2[0]);
$('.btn-select2').attr('value', 'en');

//change button stuff on click
$("#ul-id-one li").on("click", function () {
  var img2 = $(this).find('img').attr("src");
  var value2 = $(this).find('img').attr('value');
  var text2 = this.innerText;
  var item2 = '<li><img src="' + img2 + '" alt="" /><span>' + text2 + '</span></li>';
  $('.btn-select2').html(item2);
  $('.btn-select2').attr('value', value2);
  $(".ul-display").toggle();
  $(".b").css("display","none");
});

$(".btn-select2").on("click", function () {
  $(".ul-display").toggle();
  $(".ul-get-display").css("display","none");
});



