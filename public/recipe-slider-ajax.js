(function($){
  function post(action, data){
    return $.post(RecipeSliderAjax.ajaxUrl, $.extend({ action: action, _ajax_nonce: RecipeSliderAjax.nonce }, data));
  }

  $(document).on('click', '.recipe-like, .recipe-dislike', function(e){
    e.preventDefault();
    if(!RecipeSliderAjax.isUser){ 
      alert('Please log in to react.'); 
      window.location.href = wpApiSettings.root + 'wp-login.php?redirect_to=' + encodeURIComponent(window.location.href);
      return; 
    }
    var $btn = $(this), postId = $btn.data('post');
    $btn.prop('disabled', true);
    post('recipe_slider_react', { post_id: postId, type: $btn.hasClass('recipe-like') ? 'like' : 'dislike' })
      .done(function(res){
        if(res && res.success){
          var $slide = $btn.closest('.swiper-slide');
          $slide.find('.counts').text(res.data.likes);
          if(res.data.liked){ 
            $btn.addClass('is-liked'); 
          } else { 
            $btn.removeClass('is-liked'); 
          }
        }
      })
      .fail(function(xhr) {
        var errorMsg = 'An error occurred while processing your request.';
        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
          errorMsg = xhr.responseJSON.data.message;
        }
        alert(errorMsg);
      })
      .always(function() {
        $btn.prop('disabled', false);
      });
  });

  $(document).on('click', '.rate-star', function(e){
    e.preventDefault();
    if(!RecipeSliderAjax.isUser){ 
      alert('Please log in to rate.');
      window.location.href = wpApiSettings.root + 'wp-login.php?redirect_to=' + encodeURIComponent(window.location.href);
      return; 
    }
    var $star = $(this), postId = $star.data('post'), rating = $star.data('value');
    $star.prop('disabled', true);
    post('recipe_slider_rate', { post_id: postId, rating: rating })
      .done(function(res){
        if(res && res.success){
          var avg = Math.round(res.data.avg);
          if (avg < 0) avg = 0; if (avg > 5) avg = 5;
          var $slide = $star.closest('.swiper-slide');
          var stars = '★★★★★'.slice(0, avg) + '☆☆☆☆☆'.slice(0, 5-avg);
          $slide.find('.slide-rating').text(stars);
          $slide.find('.slide-actions .rate-star').each(function(i){
            var idx = i + 1; // 1..5
            var ch = idx <= avg ? '★' : '☆';
            $(this).text(ch);
          });
        }
      })
      .fail(function(xhr) {
        var errorMsg = 'An error occurred while processing your rating.';
        if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
          errorMsg = xhr.responseJSON.data.message;
        }
        alert(errorMsg);
      })
      .always(function() {
        $star.prop('disabled', false);
      });
  });
})(jQuery);


