(function ($) {

    var globals = drupalSettings.dynoblock.core;

    /*
     * Dynamic blocks controller.
     */
    var DynoBlocks = {

      regions: [],
      drupalSettings: {},

      init: function(load_ui, settings){
        this.drupalSettings = settings;
        if (load_ui && globals.load_ui) {
          DynoUi.init();
        }
        this.regions = [];
        this.loadRegions();
      },

      loadRegions: function(){
        var _this = this;
        $('.dynoblock-region').each(function(){
          var region = new DynoRegion($(this));
          _this.regions.push(region);
          region.init();
        });
        return this;
      },

      addRegion: function(region, ajax_load_blocks, rid){
        var $this = this;
        this.regions.push(new DynoRegion(region));
        if (rid && ajax_load_blocks) {
          this.ajaxLoadBlocks(rid, function(data){
            var region = $this.getRegion(rid);
            region.region.append(data.html);
            region.init();
          });
        }
      },

      getRegion: function(rid){
        for (var rrid in this.regions) {
          if (this.regions[rrid].rid == rid) {
            return this.regions[rrid];
          }
        }
      },

      getBlock: function(rid, bid, remove){
        for (var rrid in this.regions) {
          if (this.regions[rrid].rid == rid) {
            for (var bbid in this.regions[rrid].blocks) {
              if (typeof(this.regions[rrid].blocks[bbid]) != 'undefined') {
                if (this.regions[rrid].blocks[bbid].bid == bid) {
                  if (remove) {
                    delete this.regions[rrid].blocks[bbid];
                    this.regions[rrid].blocks = this.regions[rrid].blocks.filter(function(){return true;});
                  }
                  else {
                    return this.regions[rrid].blocks[bbid];
                  }
                }
              }
            }
          }
        }
      },

      getData: function(url, callback){
        $.get(url).done(function(data) {
          if (callback) {
            callback(data);
          }
        });
      },

      postData: function(url, data, callback){
        $.post(url, data).done(function(data) {
          if (callback) {
            callback(JSON.parse(data));
          }
        }, 'json');
      },

      removeBlock: function(rid, bid, callback){
        var $this = this;
        this.postData('/dynoblock/remove/' + rid + '/' + bid, [], function(data){
          $this.clearCacheTag();
          if (data.removed) {
            $this.getBlock(rid, bid, true);
          }
          if (callback) {
            callback(data);
          }
        });
      },

      updateWeight: function(rid, bid, data, callback){
        var $this = this;
        this.postData('/dynoblock/update/' + rid + '/' + bid, data, function(data){
          $this.clearCacheTag();
          if (callback) {
            callback(data);
          }
        });
      },
      
      clearCacheTag: function() {
        if(globals.cache.entity && globals.cache.id) {
          this.getData('/dynoblock/invalidate/' + globals.cache.entity + '/' + globals.cache.id, '');
        }
      }

    }

    /*
     * Dynamic region object/class controller
     */
    function DynoRegion(region){
      this.blocks = [];
      this.region = region;
      this.rid = region.data('dyno-rid');
      this.nid = region.data('dyno-nid');
      this.label = region.data('dyno-label');
      var $this = this;

      this.init = function(){
        this.loadDynoBlocks();
      }

      this.loadDynoBlocks = function(){
        var _this = this;
        this.region.children('.dynoblock').each(function(){
          _this.addBlock($(this), $(this).data('dyno-bid'), $(this).data('dyno-rid'), $(this).data('dyno-handler'));
        });
        return this.dynoblocks;
      }

      this.refresh = function(){
        this.blocks = [];
        this.loadDynoBlocks();
      }

      this.addBlock = function(block, bid, rid, handler){
        this.blocks.push(new DynoBlock(block, bid, rid, handler));
      }

      this.findBlock = function(bid){
        for(var bbid in this.blocks){
          if(this.blocks[bbid].bid == bid){
            return this.blocks[bbid];
          }
        }
      }

      this.generateBlockId = function(){
        return Math.floor(Date.now() / 1000);
      }

      /**
       * Re-organizes the regions blocks after a sort (drag and drop) event.
       */
      this.sortBlocks = function(){
        var $this = this,
          blocks = [];

        for (var i in this.blocks) {
          blocks[this.blocks[i].weight] = this.blocks[i];
        }

        blocks = blocks.sort(function (a, b) {
          if (a.weight > b.weight) {
            return 1;
          }
          if (a.weight < b.weight) {
            return -1;
          }
          // a must be equal to b
          return 0;
        });

        for (var weight in blocks) {
          this.region.append(blocks[weight].element);
          DynoBlocks.updateWeight($this.rid, blocks[weight].bid, {"weight" : weight}, function(result){});
        }
      }
    }

    /**
     * DynoBlocks UI
     * allows you to manage regions and blocks.
     */
    var DynoUi = {
      ui: null,
      UiSidebar: null,
      UiToggler: null,
      UiContent: null,
      sections: {
        regions: null,
        region: null,
        blocks: null,
      },
      step: 'regions',
      activeRegion: null,
      activeBlock: null,
      regions: {},
      scrollTimeout: null,

      init: function(){
        var $this = this;
        var ui = '<div class="menu dyno-ui closed">';
        ui += '<div class="dyno-toggle">';
        ui += '<span class="dyno-label">DynoBlocks</span><span class="dyno-expand"></span>';
        ui += '</div>';
        ui += '<div class="dyno-ui-content nav"></div>';
        ui += '</div>';
        this.ui = $(ui);
        $('body').append(this.ui);
        this.UiSidebar = this.ui.find('.dyno-toggle');
        this.UiToggler = this.ui.find('.dyno-expand');
        this.UiContent = this.ui.find('.dyno-ui-content');
        this.buildUi();
      },

      buildUi: function(){
        var $this = this;
        // Add icon to open UI.
        this.UiToggler.html('<i class="fa fa-expand" aria-hidden="true" title="Expand">');
        this.ui.css({
          minHeight: $(window).height(),
        });
        this.UiSidebar.css({
          minHeight: $(window).height(),
        });
        this.UiToggler.on('click', function(){
          $this.toggleUi();
        });
        this.UiBack = this.ui.find('.dyno-back');
        this.UiBack.on('click', function(){
          switch ($this.step) {
            case 'block':
              $this.activeBlock.element.removeClass('active');
              if($this.sections.region){
                $this.regionSelected($this.activeRegion.rid);
              }
              break;
          }
        });
      },

      event: function(event){

      },

      toggleUi: function() {
        var $this = this;
        var open = this.ui.hasClass('open') ? true : false;
        $('html').animate({
          marginRight: open === true ? '0px' : '-450px',
        }, 200);
        this.ui.animate({
          width: open === true ? '530px' : '530px',
        }, 200);
        setTimeout(function(){
          $this.ui.toggleClass('open');
          var offset = 0;
          if(!open){
            $this.initUi();
            $this.ui.css({
              top: '0px',
            });
            offset = 29;
            $this.event('ui_toggled');
          }
          // Set inner content max height.
          $this.UiContent.css({
            maxHeight: $(window).height() - offset,
            overflowY: 'scroll',
          });
        }, 200);
        // remove ui content when closed
        if (open) {
          this.onUiClose();
        }
        else {
          this.ui.removeClass('closed');
          this.UiToggler.html('<i class="fa fa-compress" aria-hidden="true" title="Collapse">');
        }
      },

      onUiClose: function(){
        // remove active if activeRegion is set
        if(this.activeRegion){
          this.activeRegion.region.removeClass('active');
        }
        // remove active if activeBLock is set
        if(this.activeBlock){
          this.activeBlock.element.removeClass('active');
        }
        // remove navigation
        this.toggleNavigation('close');
        // close ui
        this.ui.addClass('closed');
        // Add icon to expand.
        this.UiToggler.html('<i class="fa fa-compress" aria-hidden="true" title="Collapse">');
        // remove ui html
        this.UiContent.html('');
      },

      initUi: function(){
        this.UiContent.html('');
        this.initRegions();
        this.render();
      },

      initRegions: function(){
        for (var rid in DynoBlocks.regions) {
          this.addRegion(DynoBlocks.regions[rid]);
        }
      },

      loadRegionBlocks: function(region) {
        var rid = region.rid;
        this.regions[rid].blocks = {};
        var blocks = [];
        // Refresh the regions blocks.
        region.refresh();
        // Put blocks in in array by weight.
        var i = 0;
        for (var bid in region.blocks) {
          var block_id = region.blocks[bid].bid;
          this.addBlock(region.blocks[i], i);
          i++;
        }
        return this.buildBlocks(this.regions[rid]);
      },

      addBlock: function(block, key){
        var rid = this.activeRegion.rid;
        var $this = this;
        var bid = block.bid;
        var li = this.addListItem(bid, block.label);
        this.regions[rid].blocks[key] = block;
        var actions = li.find('.d-icons');
        actions.append(this.blockSortSupport(block, rid));
        actions.append(this.blockEditSupport(block, rid));
        actions.append(this.blockRemoveSupport(block, rid));

        this.regions[rid].blocks[key].el = li;
        // click listener
        this.regions[rid].blocks[key].el.find('.dyno-list-item').on('click', function(){
          $this.blockSelected(rid, $(this).parents('li').data('dyno-ui-item'), $this);
        });
        // mouseenter litener listener
        this.regions[rid].blocks[key].el.on('mouseenter mouseleave', function(){
          $this.toggleActiveBlock(rid, $(this).data('dyno-ui-item'));
        });
        return this.regions[rid].blocks[key].el;
      },

      insertBlock: function(block){
        var size = (Object.size(this.regions[this.activeRegion.rid].blocks) + 1);
        var block = this.addBlock(block, size);
        this.sections.region.find('.list-group').append(block);
      },

      addRegion: function(region){
        var $this = this;
        var rid = region.rid;
        this.regions[rid] = {};
        this.regions[rid].el = this.addListItem(rid, region.label, region.blocks.length);

        // click listener
        this.regions[rid].el.on('click', function(){
          $this.regionSelected($(this).data('dyno-ui-item'), $this);
        });
        // mouseenter litener listener
        this.regions[rid].el.on('mouseenter mouseleave', function(){
          $this.toggleActiveRegion($(this).data('dyno-ui-item'));
        });
      },

      toggleActiveRegion: function(rid){
        var region = DynoBlocks.getRegion(rid);
        if (region){
          region.region.toggleClass('active');
          var open = region.region.hasClass('active') ? true : false;
          if (open && globals.ui_scroll){
            this.scrollWindow(region.region.offset().top);
          }
        }
      },

      toggleActiveBlock: function(rid, bid){
        var block = DynoBlocks.getBlock(rid, bid);
        if (block){
          block.element.toggleClass('active');
          if (globals.ui_scroll){
            this.scrollWindow(block.element.offset().top);
          }
        }
      },

      scrollWindow: function(offset){
        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(function(){
          $('html, body').stop().animate({
            scrollTop: offset - 75,
          }, 200);
        }, 500);
      },

      addListItem: function(id, label, tag){
        var li = $('<li data-dyno-ui-item="'+id+'" class="list-group-item"></li>');
        var label = label ? label : id;
        // add tag if needed
        if(tag) li.append('<span class="badge label label-default label-pill pull-right">' + tag + '</span>');
        // add li text
        li.append('<span class="dyno-list-item">' + label + '</span><span class="d-icons"></span>');
        return $(li);
      },

      blockRemoveSupport: function(block, rid){
        var $this = this;
        var removeable = $('<a href="#" class="btn btn-danger dynoblock-remove"><i class="fa fa-trash-o" aria-hidden="true" title="Delete"></a>');
        removeable.on('click', function(e){
          e.preventDefault();
          var remove = confirm('Are you sure you want to delete this block?');
          if (remove == true) {
            block.remove();
            for (var delta in $this.regions[rid].blocks) {
              if ($this.regions[rid].blocks[delta].bid == block.bid) {
                $this.regions[rid].blocks[delta].el.remove();
              }
            }
          }
        });
        return removeable;
      },

      blockEditSupport: function(block, rid){
        var $this = this;
        var editable = $this.makeAJAXLink(block.bid,'<i class="fa fa-edit" aria-hidden="true" title="Edit">', 'editform', rid);
        return editable;
      },

      blockSortSupport: function(block, rid){
        var $this = this;
        var sortable = $('<a href="#"><i class="fa fa-arrows sort-row" aria-hidden="true" title="Sort"></a>');
        var bid = block.bid;
        sortable.on('click', function(e) {
          e.preventDefault();
        });
        return sortable;
      },

      addBlockSortSupport: function(){
        // sortable regions
        var $this = this;
        var rid = this.activeRegion.rid;
        setTimeout(function(){
          $this.sections.region.find('.list-group').addClass('dyno-sortable active').sortable({
            axis: "y",
            cursor: "progress",
            stop: function(event, ui){
              var i = 0;
              $(this).children().each(function(){
                var block = DynoBlocks.getBlock(rid, $(this).data('dyno-ui-item'));
                if (block) {
                  block.weight = i;
                }
                i++;
              });
              $this.activeRegion.sortBlocks();
            },
          });
        }, 1000);
      },

      removeBlockSortSupport: function(){
        // sortable regions
      },

      renderRegions: function(){
        this.sections.regions = $('<ul class="list-group"></ul>');
        this.UiContent.append(this.sections.regions);
        for(var li in this.regions){
          this.sections.regions.append(this.regions[li].el);
        }
      },

      buildBlocks: function(region){
        var blocks = $('<ul class="list-group"></ul>');
        if(!$.isEmptyObject(region.blocks)){
          for(var bid in region.blocks){
            blocks.append(region.blocks[bid].el);
          }
        } else {
          blocks.append('<li class="list-group-item">No Blocks Found. Click <a class="dyno-add-block">here</a> to add one.</li>');
        }
        return blocks;
      },

      createBlockWeight: function(weights, weight){
        if(weights.indexOf(weight) > -1){
          return this.createBlockWeight(weights, (weight + 1));
        } else {
          return weight;
        }
      },

      buildBlockDisplay: function(block){

      },

      regionSelected: function(rid, container){
        var region = DynoBlocks.getRegion(rid);
        if (region) {
          this.activeRegion = region;
          this.sections.region = $('<div class="dyno-ui-region"></div>');
          this.sections.region.append(this.sectionHeader('region', region));
          this.sections.region.append(this.loadRegionBlocks(region));
          this.UiContent.html(this.sections.region);
          this.step = 'region';
          this.toggleNavigation('open');
          // Add handler for back button.
          this.UiBack = this.UiContent.find('.dyno-back');
          this.UiBack.on('click', function() {
            container.initUi();
            container.toggleNavigation('close');
            container.activeRegion.region.removeClass('active');
          });
          // Re-attach behaviors for modal link.
          Drupal.attachBehaviors(document);
        }
      },

      blockSelected: function(rid, bid, container){
        var block = DynoBlocks.getBlock(rid, bid);
        if (block) {
          this.sections.block = $('<div class="dyno-ui-block"></div>');
          this.sections.block.append(this.sectionHeader('block', block));
          this.sections.block.append(this.buildBlockDisplay(block));
          this.sections.region.remove();
          this.UiContent.html(this.sections.block);
          this.activeBlock = block;
          this.step = 'block';
          // Add handler for back button.
          this.UiBack = this.UiContent.find('.dyno-back');
          this.UiBack.on('click', function() {
            container.activeBlock.element.removeClass('active');
            if (container.sections.region) {
              container.regionSelected(container.activeRegion.rid);
            }
          });
        }
      },

      sectionHeader: function(type, item){
        var header = $('<div class="dyno-ui-header"></div>');
        var label = item.label ? item.label : item.rid;
        if (label.length > 44) label = label.substring(0, 44) + '...';
        var actions = $('<div class="dyno-ui-actions"></div>').appendTo(header);
        var back = '<span class="dyno-back action"><i class="fa fa-home" aria-hidden="true" title="Back"></span>';

        switch (type) {
          case 'region':
            var $this = this;
            if (!$this.sections.region.hasClass('dyno-editable')) {
              $this.addBlockSortSupport();
            }
            else {
              $this.removeBlockSortSupport();
            }
            $this.sections.region.toggleClass('dyno-editable');
            actions.append(back);
            var add = $this.makeAJAXLink('new','<i class="fa fa-plus fa-fw" aria-hidden="true" title="Add Dynoblock"></i>', 'selectgroup', item.rid);
            actions.append(add);
            actions.append('<span class="region-title">' + label + '</span>');
            break;
          case 'block':
            actions.append(back);
            actions.append('<div><strong>ID: </strong><span>'+ label +'</span></div>');
            actions.append('<div><strong>Component: </strong><span>'+ item.handler +'</span></div>');
            break;
        }
        return header;
      },

      toggleNavigation: function(state){
        switch (state) {
          case 'open':
            this.ui.addClass('show-back');
            break;
          case 'close':
            this.ui.removeClass('show-back');
            break;
        }
      },

      render: function(){
        // render the regions into the sidebar
        this.renderRegions();
      },

      makeAJAXLink: function(bid, text, step, rid){
        var href = '/dynoblock/admin-wizard/ajax/' + step + '?bid=' + bid + '&rid=' + rid + '&etype=' + globals.cache.entity + '&eid=' + globals.cache.id;

        return $('<a>', {
          'html': text,
          'href': href,
          'data-dialog-type': 'modal',
          'class': 'use-ajax',
          'data-dialog-options': '{"width":800,"height":600}'
        });

      }

    }

    /*
     * Dynamic block object/class
     */
    function DynoBlock(block, bid, rid, handler){
      this.element = block;
      this.bid = bid;
      this.label = block.data('dyno-label');
      this.weight = block.data('dyno-weight');
      this.handler = handler;
      this.rid = rid;
      this.init = function(){}
      this.old_html = null;
      var actionTimer, $this = this;

      this.removeActions = function(){
        this.element.find('.dynoblock-actions').remove();
      }

      this.remove = function(){
        // # TODO remove block from this classes this.blocks object
        DynoBlocks.removeBlock(this.rid, this.bid, function(result){
          if(result.removed){
            $this.element.remove();
          }
        });
      }


    }


  Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
      if (obj.hasOwnProperty(key)) size++;
    }
    return size;
  };

  var init = false;
  Drupal.behaviors.dynoblock = {
    attach: function (context, settings) {
      if (!init) {
        init = true;
        // Run Dynoblocks.
        DynoBlocks.init(true, settings);
        Drupal.DynoBlocks = DynoBlocks;
        Drupal.DynoBlocksUi = DynoUi;
      }
    }
  };

})(jQuery);
