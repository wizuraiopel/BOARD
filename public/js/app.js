// Simple app JS for global behaviors
(function(){
    if (typeof jQuery === 'undefined') return;
    jQuery(function($){
        // Highlight active link
        var path = window.location.href;
        $('.sidebar a').each(function(){
            try{
                if (this.href === path || path.indexOf(this.getAttribute('href')) !== -1){
                    $(this).addClass('active');
                }
            }catch(e){}
        });
        
        // AJAX page loader for sidebar links (single-page feel)
        function isAjaxLink(href) {
            try {
                var url = new URL(href);
                var action = url.searchParams.get('action');
                if (!action) return false;
                if (action === 'logout' || action === 'login') return false; // let full navigation
                return true;
            } catch(e) { return false; }
        }

        function loadPage(href, addHistory) {
            if (addHistory === undefined) addHistory = true;
            fetch(href, { credentials: 'same-origin' })
                .then(function(resp){ return resp.text(); })
                .then(function(html){
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var newContent = doc.querySelector('.content-wrap');
                    if (newContent) {
                        $('.content-wrap').replaceWith(newContent.cloneNode(true));
                        // update active link classes
                        $('.sidebar a').removeClass('active');
                        $('.sidebar a').each(function(){ if (this.href === href || href.indexOf(this.getAttribute('href')) !== -1) $(this).addClass('active'); });
                        // update title
                        if (doc.title) document.title = doc.title;
                        if (addHistory) history.pushState({url: href}, '', href);

                        // Dispatch a custom event so page scripts can re-initialize
                        try {
                            document.dispatchEvent(new Event('inventra:pageLoaded'));
                        } catch (e) { /* ignore */ }
                    } else {
                        // fallback to full navigation
                        window.location.href = href;
                    }
                }).catch(function(err){
                    console.error('AJAX load failed', err);
                    window.location.href = href; // fallback
                });
        }

        // Intercept sidebar clicks
        $('.sidebar').on('click', 'a', function(e){
            var href = this.href;
            if (isAjaxLink(href)) {
                e.preventDefault();
                loadPage(href, true);
            }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', function(e){
            var url = (e.state && e.state.url) ? e.state.url : window.location.href;
            // load without pushing history
            loadPage(url, false);
        });
    });
})();
