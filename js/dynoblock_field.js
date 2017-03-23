(function ($) {
  $(document).ready(function(){
      
      var DynoBlockFieldUi = {
        
        init: function(){
          this.form = $('#bt-dynoblock-form');
          this.addThemeLinks();
        },
        
        getData: function(url, data, callback){
    			$.get(url, data).done(function(data) {
            if(callback){
              callback(data);
            }
          });
  			},
        
        addThemeLinks: function(){
          var $this = this;
          this.form.find('.form-item').each(function(){
            $this.addThemeLink($(this));
          });
        },
        
        addThemeLink: function(e){
          var $this = this;
          var input = e.find('input');
          var edit = $('<a href="#" class="dyno-field-theme" data-dyno-type="'+input.attr('type')+'">theme</a>');
          e.append(edit);
          edit.on('click', function(){
            $this.onThemeClick($(this));
          });
        },
        
        onThemeClick: function(e){
          this.type = $(e.data('dyno-type'));
          this.buildModal(e);
        },
        
        buildModal: function(){
          var modal = '<div id="widget-form-modal" class="modal fade" tabindex="-1" role="dialog">';
          modal+= '<div class="modal-dialog">';
            modal+= '<div class="modal-content">';
              modal+= '<div class="modal-header">';
                modal+= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                modal+= '<h4 class="modal-title">Select Theme</h4>';
              modal+= '</div>';
              modal+= '<div class="modal-body">';
              modal+= '</div>';
              modal+= '<div class="modal-footer">';
                modal+= '<button type="button" class="btn cancel btn-default" data-dismiss="modal">Close</button>';
                modal+= '<button type="button" class="btn save btn-primary">Save changes</button>';
              modal+= '</div>';
              modal+= '</div>';
            modal+= '</div>';
          modal+= '</div>';
          this.modal = $(modal);
          this.addModalContent();
          this.modalEvents();
          $('body').append(this.modal);
          this.modal.modal("toggle");
        },
        
        modalEvents: function(){
          var $this = this;
          this.modal.find('.cancel').on('click', function(){
              $this.modal.toggle();
          });
        },
        
        addModalContent: function(e){
          console.log(this);
          this.getData('dynoblock/themes', {"type" : 'button'}, function(themes){
            this.modal.find('.modal-body').html();
          });
        }
        
      }
      
      DynoBlockFieldUi.init();
  });
})(jQuery);