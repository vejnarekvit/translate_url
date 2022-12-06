( function ( wp ) {
  // set important variables
  var el = wp.element.createElement;
  var registerPlugin = wp.plugins.registerPlugin;
  var SettingPanel = wp.editPost.PluginDocumentSettingPanel;
  var RawHTML = wp.element.RawHTML;
  var languages = vars.languages;
  var targetURL = vars.URLToApi;

  // function that refister new plugin
  function registerNewPlugin(resultHTML) {
    registerPlugin( 'translate-url', {
      render: function () {
        return el(
        SettingPanel,
        {
          className: 'setting-panel-translate-url',
          title: 'Translate URL',
        },
        RawHTML ({
          children: resultHTML,
        }),
        el (
          "button",
          { onClick: handleClick, className: 'button-primary' },
          "Translate"
        ),
        el (
          "div",
          {className: 'lds-dual-ring', id: 'lds-dual-ring'}
        ),
      )}
    });
  }


  var handleClick = function handleClick(event) {
    const urlParams = new URLSearchParams(window.location.search);
    const post = urlParams.get('post');
    var selectLanguages = document.getElementById("languages-select");
    var value = selectLanguages.options[selectLanguages.selectedIndex].value;
    var UrlWithGetParam = `${targetURL}/translate_url/php-scripts/get_content.php?post=${post}&lang=${value}`;

    var xmlhttp = new XMLHttpRequest()
    xmlhttp.onreadystatechange = function(res) {
      if (this.readyState == 4 && this.status == 200) {
        var result = JSON.parse(this.response);
        if (result.errors) {
          var errors = result.errors; 
          var message = "";
          Object.keys(errors).forEach(key => {
            Object.keys(errors[key]).forEach(value => {
              message += errors[key][value];
              message += "<br>";
            });
          });

          wp.data.dispatch('core/notices').createWarningNotice(
            message,
            {
              __unstableHTML: true, // true = allows HTML; default false
              isDismissible: true,
              id: 'translate-url-warning'
            }
          );
        }
        if (typeof result.content !== 'undefined') {
          console.log('Post edited');
          console.log(result.content);
          // reset all blocks in post with brand new data
          wp.data.dispatch( 'core/block-editor' ).resetBlocks( wp.blocks.parse( result.content ) );
        }
      }
    }

    xmlhttp.open("GET", UrlWithGetParam, false);
    xmlhttp.send(null);
  };
  
  // create HTML as a result for sidebar
  var resultHTML = "<select id='languages-select' style='float:left; width: 80%; margin-bottom: 15px;'>";
  for (var i = 0; i < languages.length; i++) {
    resultHTML += `<option value="${languages[i]['slug']}">${languages[i]['name']}</option>`;
  }
  resultHTML += "</select>"; 
  registerNewPlugin(resultHTML)
  
  
} )( window.wp );

