/**
 * @file Main application JS file.
 */

// When the document is ready:
$(function () {
  
  // Initialize the Google Map.
  var mapOptions = {
    center: new google.maps.LatLng(40, -95),
    zoom: 3,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  var map = new google.maps.Map(document.getElementById("map"), mapOptions);
  
  // Set up a global geocoder object for geocoding later.
  var geocoder = new google.maps.Geocoder();
  
  // Define an error handler using the smoke library.
  function error(text) {
    smoke.alert(text);
  }
  
  // Set up the fixed behavior for the map.
  var $mapWrapper = $('#map-wrapper');
  var initialOffset = parseInt($mapWrapper.offset().top);
  $(window).scroll(function () {    
      var windowTop = $(window).scrollTop();
      // If the window is scrolled far enough, convert the map to fixed positioning.
      if (windowTop > initialOffset - 10){
        $mapWrapper.css({ position: 'fixed', top: 10, width: 460});
          }
      else {
        $mapWrapper.css({position:'static'});
      }
    });
  
  /**
   * Backbone Objects.
   */
  // Model definition for a favorite.
  var Favorite = Backbone.Model.extend({
    urlRoot: '/fav'
  });

  // Collection definition for a collection of favorites.
  var FavoriteCollection = Backbone.Collection.extend({
    model:Favorite,
    url:'/fav'
  });
  
  // Create the collection.
  var favorites = new FavoriteCollection();

  // View definition for a favorite.
  var FavoriteView = Backbone.View.extend({
    tagName: 'div',
  
    // Cache the template function.
    template: _.template($("#favorite-template").html()),
    
    // Register events.
    events: {
      'click .favorite-delete' : 'clear',
      'dblclick .favorite' : 'edit',
      'click .favorite-save' : 'doneEditing',
      'keypress input'  : 'updateOnEnter'
    },
    
    initialize: function () {
    
      // Register listeners for changes on the model.
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
      
      // Add the marker to the google map.
      this.addMarker();
    },
  
    // Render the template.
    render: function () {
      var content = this.template(this.model.toJSON());
      this.$el.html(content);
      return this;
    },
    
    // Remove a favorite.
    clear: function () {
      this.removeMarker();
      this.model.destroy();
      return false;
    },

    // Activate edit mode.
    edit: function () {
      this.$el.addClass('edit-in-place').find('input').last().focus();
      
    },
    
    // Handles submiting the form via the enter key.
    updateOnEnter: function (e) {
      if (e.keyCode == 13) this.doneEditing();
    },
    
    // Complete the edit process.
    doneEditing: function () {
      // Gather the user input.
      var values = {
        address: this.$el.find('.favorite-address-input input').val(),
        name: this.$el.find('.favorite-name-input input').val()
      }
      
      // If both of the text fields are empty, delete this favorite.
      if (!values.address && !values.name) {
        this.clear();
      }
      else {
        
        // Clear out the map marker, a new one will be created after geocoding.
        this.removeMarker();
        // Set the new values on the model.
        this.model.set(values);
        // Geocode the address. This also saves the model.
        this.geocode();
        // We're no longer editing in place.
        this.$el.removeClass('edit-in-place');
      }
      return false;
    },
    
    // Get coordinates from Google and save the model.
    geocode: function () {
      var that = this;
      geocoder.geocode({address: this.model.get('address')}, function (results, status) {
        // Only proceed if the coordinates are found.
        if (status == 'OK') {
          var latlon = results[0].geometry.location;
          that.model.set({
            lat:latlon.lat(), 
            lon: latlon.lng()
          });
          // Add a marker to the google map at the appropriate location.
          that.addMarker();
          
          // Save the model with the new lat and lon.
          that.model.save();
        }
        else {
          // Warn the user that the address could not be geocoded, and turn on editing
          // for the favorite.
          error('That address could not be found.');
          that.edit();
        }
      });
    },
    
    // Add a marker to the Google map.
    addMarker: function () {
      this.marker = new google.maps.Marker({
        position: new google.maps.LatLng(this.model.get('lat'), this.model.get('lon')),
        map: map,
        title: this.model.get('name')
      });

    },
    
    // Remove the marker from the Google map.
    removeMarker: function () {
      // Only try to remove the marker if there is one.
      if (this.marker) {
        this.marker.setMap(null);
      }
    }
    
  });

  // View definition for the application view.
  var AppView = Backbone.View.extend({
    el: $("#right-column"),
    
    // Register click and key events.
    events: {
      'keypress .new-text-element' : 'updateOnEnter',
      'click .button-link.favorite-add' : 'createFavorite'
    },
    
    initialize: function () {
      // Register listeners on the collection.
      this.listenTo(favorites, 'add', this.addOne);
      this.listenTo(favorites, 'reset', this.addAll);
      this.listenTo(favorites, 'remove', this.addEmptyText);
      
      // Fetch the full list of favorites.
      favorites.fetch();
    },
    
    // Handles submission of the new favorite form.
    createFavorite: function () {
      // Extract the form fields.
      var name = $('.favorite-new-name-input input').val();
      var address = $('.favorite-new-address-input input').val();
      
      // Make sure that both fields have been filled out.
      if (!name || !address) {
        error('Both a name and an address are required.');
      }
      else {
        // Create the new model.
        favorite = new Favorite({
          name:name, 
          address:address
        });
        
        // Add the model to the collection.
        favorites.add(favorite);
        
        // Clear the form.
        $('.favorite-new-input input').val('');
      }
      return false;
    },
    
    // Handle an enter key press on the new favorite form.
    updateOnEnter: function (e) {
      if (e.keyCode == 13) this.createFavorite();
    },
    
    // Fires when a new 
    addOne: function (favorite) {
      var view = new FavoriteView({
        model: favorite
      });
      
      if (!favorite.has('lat')) {
        view.geocode();
      }

      this.$el.find('#list-wrapper').prepend(view.render().el);

      this.removeEmptyText();
      return view;
    },
    
    addAll: function () {
      favorites.each(this.addOne, this);
    },
    
    removeEmptyText: function () {
      $('#empty-text-wrapper').hide();
    },
    
    addEmptyText: function () {
      if (!favorites.size()) {
        $('#empty-text-wrapper').show();
      } 
    }
  });

  var app = new AppView();
});