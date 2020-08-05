(function($) {

    /** global: Craft */
    /** global: Garnish */
    /**
     * Froala Editor input class
     */
    Craft.FroalaEditorInput = Craft.FroalaEditorConfig.extend({
        afterInit: function() {
            this.id = this.settings.id;

            Craft.FroalaEditorInput.currentInstance = this;

            // Initialize Froala
            this.$textarea = new FroalaEditor('#' + this.id, this.config);

            delete Craft.FroalaEditorInput.currentInstance;
        }
    });

})(jQuery);