(function($) {
    $(document).ready(function() {
      
        var DynoSelectors = {
          layout: '.dyno-layout',
          region: '.dyno-region',
          block:  '.dyno-block',
        }
          
        var DynoAttrs = {
          lid: 'dyno-lid',
          rid: 'dyno-rid',
          bid: 'dyno-bid',
          layout: 'data-dyno-layout',
        }
      
        /**
         * controller of all controllers
         */
        var DynoBlocksCore = {
          
          layouts: {},
          
          /**
           * initializes all controllers
           */
          init: function(){
            var $this = this;
            $(DynoSelectors.layout).each(function(){
              var layout = new DynoLayout($(this));
              $this.layouts[$(this).data(DynoAttrs.lid)] = layout;
              layout.setRegions();
            });
          },
          
        };
        
        
        /**
         * controller for dyno layouts
         */
        var DynoLayoutController = {
          
          layouts: [],
          
          /**
           * initializes all layouts for the page.
           */
          initLayouts: function(){

          },
          
        };
        
        
        /**
         * controller for dyno regions
         */
        var DynoRegionController = {
          
          /**
           * initializes all regions for a particular layout.
           */
          initRegions: function(){  

          },
          
        };
        
        /**
         * controller for dyno blocks
         */
        var DynoBlockController = {
          
          /**
           * initializes all blocks
           */
          initBlocks: function(){
            
          },
          
        };
        
        
        
        
        /**
         * Dummy layout
         */
        function DynoLayout(layout) {
          this.element = layout;
          this.regions = {};
          var $this = this;
          this.setRegions = function(){
            layout.find(DynoSelectors.region).each(function(){
              $this.regions[$(this).data(DynoAttrs.rid)] = new DynoRegion($(this));
            });
          }
        }
        
        /**
         * Dummy region
         */
        function DynoRegion(region) {
          this.element = region;
          this.blocks = {};
          var $this = this;
          this.rid = region.data(DynoAttrs.rid);
          region.find(DynoSelectors.block).each(function(){
            $this.blocks[$(this).data(DynoAttrs.bid)] = new DynoBlock($(this));
          });
        }
        
        /**
         * Dummy block
         */
        function DynoBlock(block) {
          this.element = block;
          this.bid = block.data(DynoAttrs.bid);
        }
        
        
        
        
        DynoBlocksCore.init();
        console.log(DynoBlocksCore);
    });
})(jQuery);