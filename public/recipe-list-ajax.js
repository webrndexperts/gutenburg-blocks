(function ($) {
  'use strict';

  function serializeForm(form) {
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
      if (formData.getAll(key).length > 1) {
        formData.getAll(key).forEach(val => {
          params.append(key + '[]', val);
        });
      } else {
        params.append(key, value);
      }
    }
    
    return params.toString();
  }

  function handleSubmit(form, e) {
    if (e) { 
      e.preventDefault(); 
    }
    
    const wrapper = form.closest('.recipe-list');
    const grid = wrapper.querySelector('.recipe-list__grid');
    const pager = wrapper.querySelector('.recipe-list__pagination');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
    
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Loading...';
    }
    
    if (e && !e.target.closest('.recipe-list__pagination')) {
      form.querySelector('input[name="rl_page"]').value = 1;
    }
    
    const params = serializeForm(form);
    const urlParams = new URLSearchParams(params);
    
    if (window.history && window.history.pushState) {
      const newUrl = window.location.pathname + '?' + urlParams.toString();
      window.history.pushState({}, '', newUrl);
    }
    
    const formData = new FormData();
    formData.append('action', 'recipe_list_query');
    formData.append('_ajax_nonce', RecipeListAjax.nonce);
    
    for (const [key, value] of new FormData(form).entries()) {
      formData.append(key, value);
    }
    
    fetch(RecipeListAjax.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(res => {
      if (!res || !res.success) {
        throw new Error('Invalid response from server');
      }
      
      if (grid) {
        grid.innerHTML = res.data.html || '<p>No recipes found.</p>';
      }
      
      if (pager) {
        pager.innerHTML = res.data.pagination || '';
      }
      
      if (grid) {
        grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      if (grid) {
        grid.innerHTML = '<p>Error loading recipes. Please try again.</p>';
      }
    })
    .finally(() => {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    });
  }

  function handlePaginate(e) {
    const a = e.target.closest('.recipe-list__pagination a');
    if (!a) return;
    const wrapper = a.closest('.recipe-list');
    const form = wrapper.querySelector('.recipe-list__controls');
    if (!form) return;
    e.preventDefault();
    const url = new URL(a.href, window.location.href);
    const page = url.searchParams.get('rl_page') || url.searchParams.get('paged') || '1';
    form.querySelector('input[name="rl_page"]').value = page;
    handleSubmit(form);
  }

  document.addEventListener('submit', function (e) {
    if (e.target && e.target.classList.contains('recipe-list__controls')) {
      handleSubmit(e.target, e);
    }
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('.recipe-list__pagination a')) {
      handlePaginate(e);
    }
  });
})(jQuery);
