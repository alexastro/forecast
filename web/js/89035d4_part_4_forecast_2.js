function forecastViewModel() {
    
    // Data
    var self = this;
    self.sections = ['Forecast', 'Check Date', 'Locations', 'Notifications'];
    self.dateFormat = 'yyyy-mm-dd';
    self.dateInputFormat = 'dd.mm.yyyy';
    self.dateInputJSFormat = 'd.m.Y';
    self.timeFormat = 'HH:MM';
    self.chosenForecast = ko.observable();
    self.chosenSectionId = ko.observable();
    self.emptyForecast = ko.observable();
    self.showWeatherForm = ko.observable(false);
    self.showLocationForm = ko.observable();
    self.showNotificationForm = ko.observable(false);
    
    self.emptyWeatherRow = {
       'time'          : '', 
       'temp'          : '', 
       'windSpeed'     : '', 
       'windDirection' : '', 
       'rainChance'    : '',
       'humidity'      : ''
    };
    
    self.emptyLocationRow = {
       'id'   : null,
       'name' : '', 
       'lat'  : '', 
       'lng'  : ''
    };
   
    self.emptyNotificationRow = {
       'id'         : null, 
       'locationId' : null,
       'location'   : null,
       'email'      : '', 
       'to_hot'     : '', 
       'to_cold'    : '',
       'strong_wind': ''
    };
   
    self.weatherEntry = ko.observable(jQuery.extend({}, self.emptyWeatherRow));
    self.locationEntry = ko.observable(jQuery.extend({}, self.emptyLocationRow));
    self.notificationEntry = ko.observable(jQuery.extend({}, self.emptyNotificationRow));
    // Locations
    (typeof(locations) != 'undefined') || (locations = []);
    
    self.availableLocations = ko.observableArray(locations);
    self.selectedLocationId = ko.observable();
    self.selectedLocationId.extend({ rateLimit: 400 });
    
    self.notifications = ko.observableArray(); 
    var date = new Date();
    self.chosenDate = ko.observable(date); 
    self.chosenDateTxt = ko.computed(function() {
        return self.chosenDate().format(self.dateInputFormat);
    });
    self.weatherForm = $('#edit-weather-form');
    self.locationForm = $('#edit-location-form');
    self.notificationForm = $('#edit-notification-form');
    
    // Weather
    self.availableWindDirs = ['N', 'NE', 'E', 'ES', 'S', 'SW', 'W', 'NW'];
    
    // Behaviours    
    self.goToSection = function(section) { location.hash = section };
    
    // Entity controllers
    self.deleteWeather = function(weather, event) {
        
        if (!confirm('The record will be deleted. Continue?')) {
            return false;
        }
        
        var dataWeather = {};
        dataWeather.timestamp = weather.timestamp;
        dataWeather.locationId = self.selectedLocationId();
        var date = new Date(dataWeather.timestamp*1000); // from unixtimestamp
        
        // send
        $.ajax({
            type: 'DELETE',
            url: '/app_dev.php/forecast/weather' + '?' + $.param(dataWeather),
            success: function() {
                // update forecast block
                self.loadForecast(self.selectedLocationId(), date, true);
                
                var target = $( event.target );
                var row = $(target.parents('.records .row'));
                if (row.length > 0) {
                    row.hide();
                }
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
    };
    // open create/edit weather form
    self.editWeather = function(weather, event) {
        
        self.closeWeatherForm();
        
        if (weather.hasOwnProperty('timestamp')) {
            // edit mode
            self.weatherEntry(weather);
            self.weatherForm.addClass('absolute').show();
        }
        else {
            //create mode
            self.weatherEntry(jQuery.extend({}, self.emptyWeatherRow));
        }
        
        var target = $( event.target );
        var row = $(target.parents('.records .row'));
        if (typeof(weather) == 'object' && row.length > 0) {
//            // edit mode: place form instead of original row 
            row.addClass('edited');
            self.weatherForm.css('top', row.offset().top+'px');
        }
        else {
            // place form in the end of forecast block
           self.weatherForm.removeClass('absolute').show();
        }
        self.showWeatherForm(true);
        $(self.weatherForm.find('input.timepicker')).timepicker({ 'timeFormat': 'H:i', 'step': 60 });
    };
    
    // process weather form
    self.updateWeather = function(weatherForm) { 
        //fetch values
        var weather = {};
        for (var i in self.emptyWeatherRow) {
            var elName = 'upd_'+i;
            var element = $($(weatherForm).find('#'+elName));
            if (element.length && typeof(element.attr('id')) != 'undefined') {
                var name = element.attr('id').replace('upd_', '');
                if (name == 'time') {
                    weather[name] = element.timepicker('getTime', self.chosenDate()); 
                }
                else {
                    weather[name] = element.val();
                }    
            }
        };
        
        // validate
        var errors = [];
        if (typeof(weather.time) == 'undefined' || weather.time === '') {
            errors.push('Time not set');
        }
        
        if (typeof(weather.temp) == 'undefined' || weather.temp === '') {
            errors.push('Temperature not set');
        }
        
        if (typeof(weather.windSpeed) == 'undefined') {
            errors.push('Wind speed not set');
        }
        else if (isNaN(parseFloat(weather.windSpeed))) {
            errors.push('Temp is not numeric');
        }
        
        if (typeof(weather.windSpeed) == 'undefined' || weather.windSpeed === '') {
            errors.push('Wind speed not set');
        }
        
        if (typeof(weather.windDirection) == 'undefined' || weather.windDirection === '') {
            errors.push('Wind direction not set');
        }
        
        if (typeof(weather.rainChance) == 'undefined') {
            errors.push('Rain Chance not set');
        }
        else if (isNaN(parseFloat(weather.rainChance))) {
            errors.push('Rain Chance is not numeric');
        }
        else if (!(weather.rainChance >=0 && weather.rainChance <= 100)) {
            errors.push('RainChance should be in (0..100) range.');
        }
        
        if (typeof(weather.humidity) == 'undefined') {
            errors.push('Humidity not set');
        }
        else if (isNaN(parseFloat(weather.humidity))) {
            errors.push('Humidity is not numeric');
        }
        else if (!(weather.humidity >=0 && weather.humidity <= 100)) {
            errors.push('Humidity should be in (0..100) range.');
        }
        
        if (errors.length > 0) {
            alert('Validation error: '+errors.join('; '));
            
            return false;
        }
        
        if (self.weatherEntry() && self.weatherEntry().hasOwnProperty('timestamp')) {
            weather.oldTimestamp = self.weatherEntry().timestamp;
        }
        
        weather.timestamp = weather.time.getTime() / 1000;
        delete weather.time;
        weather.locationId = self.selectedLocationId();
        
        $(self.weatherForm.find('[type="submit"]')).addClass('ajx-loading');
        // send
        $.ajax({
            type: 'POST',
            url: '/app_dev.php/forecast/weather',
            data: weather,
            success: function() {
                // update forecast block
                self.loadForecast(weather.locationId, self.chosenDate(), true);
                //hide form
                self.closeWeatherForm();
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
        
        return false;
    };
    
    self.closeWeatherForm = function(){
        
        // hide form
        self.weatherForm.removeClass('absolute').hide(); 
        // show all hidden rows
        $('.records .row').removeClass('edited');
        self.showWeatherForm(false);
        $(self.weatherForm.find('[type="submit"]')).removeClass('ajx-loading');
        return false;
    };
    
    self.deleteLocation = function(curLocation, event) {
        
        if (!confirm('The record will be deleted. Continue?')) {
            return false;
        }
        
        var data = {
            id: curLocation.id
        };
        
        // send
        $.ajax({
            type: 'DELETE',
            url: '/app_dev.php/forecast/location' + '?' + $.param(data),
            success: function() {
                // clear cache for selected location
                for (var key in cacheData) {
                    if (key.indexOf(curLocation.id+'__')!==-1) {
                        delete cacheData[key];
                    }
                }
                // remove location record
                for (var i in self.availableLocations()) {
                    var row = (self.availableLocations())[i];
                    if (row.id == curLocation.id) {
                        self.availableLocations.splice(i, 1);
                    }
                }
                
                var target = $( event.target );
                var row = $(target.parents('.records .row'));
                if (row.length > 0) {
                    row.hide();
                }
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
    };
    
    // open create/edit location form
    self.editLocation = function(curLocation, event) {
        
        self.closeLocationForm();
        
        self.showLocationForm(true);
        if (curLocation.id > 0) {
            // edit mode
            self.locationEntry(curLocation);
            self.locationForm.addClass('absolute').show();
        }
        else {
            //create mode
            self.locationEntry(jQuery.extend({}, self.emptyLocationRow));
        }
        
        var target = $( event.target );
        var row = $(target.parents('.records .row'));
        if (typeof(curLocation) == 'object' && row.length > 0) {
//            // edit mode: place form instead of original row 
            row.addClass('edited');
            self.locationForm.css('top', row.offset().top+'px');
        }
        else {
            // place form in the end of forecast block
           self.locationForm.removeClass('absolute').show();
        }
        
    };
    
    // process location form
    self.updateLocation = function(locationForm) { 
        //fetch values
        var curLocation = {};
        for (var i in self.emptyLocationRow) {
            var elName = 'upd_'+i;
            var element = $(self.locationForm.find('#'+elName));
            if (element.length && typeof(element.attr('id')) != 'undefined') {
                var name = element.attr('id').replace('upd_', '');
                curLocation[name] = element.val();
            }
        };
        
        // validate
        var errors = [];
        if (typeof(curLocation.name) == 'undefined' || !curLocation.name) {
            errors.push('Name not set');
        }
        
        if (errors.length > 0) {
            alert('Validation error: '+errors.join('; '));
            
            return false;
        }
        
        if (self.locationEntry().id > 0 ) {
            curLocation.id = self.locationEntry().id;
        }
        else {
            delete curLocation.id;
        }
        
        $(self.locationForm.find('[type="submit"]')).addClass('ajx-loading');
        
        // send
        $.ajax({
            type: 'POST',
            url: '/app_dev.php/forecast/location',
            data: curLocation,
            success: function(data) {
                
                if (curLocation.hasOwnProperty('id')) {
                    // clear cache for selected location
                    for (var key in cacheData) {
                        if (key.indexOf(data.id+'__')!==-1) {
                            delete cacheData[key];
                        }
                    }
                    // update locations
                    for (var i in self.availableLocations()) {
                        var row = (self.availableLocations())[i];
                        if (row.id == data.id) {
                            self.availableLocations.splice(i, 1, data);
                        }
                    }
                }
                else {
                    // append locations list
                    self.availableLocations.push(data);
                }
                
                //hide form
                self.closeLocationForm();
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
        
        return false;
    };
    
    self.closeLocationForm = function(){
        // hide form
        self.showLocationForm(false);
        self.locationForm.removeClass('absolute').hide(); 
        // show all hidden rows
        $('#locations .records .row').removeClass('edited');
        $(self.locationForm.find('[type="submit"]')).removeClass('ajx-loading');
    };
    
    
    self.deleteNotification = function(notification, event) {
        
        if (!confirm('The record will be deleted. Continue?')) {
            return false;
        }
        
        var data = {
            id: notification.id
        };
        
        // send
        $.ajax({
            type: 'DELETE',
            url: '/app_dev.php/forecast/notification' + '?' + $.param(data),
            success: function() {
                // remove notification record
                for (var i in self.notifications()) {
                    var row = (self.notifications())[i];
                    if (row.id == notification.id) {
                        self.notifications.splice(i, 1);
                    }
                }
                
                var target = $( event.target );
                var row = $(target.parents('.records .row'));
                if (row.length > 0) {
                    row.hide();
                }
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
    };
    
    // open create/edit notification form
    self.editNotification = function(notification, event) {
        
        self.closeNotificationForm();
        
        self.showNotificationForm(true);
        if (notification.id > 0) {
            // edit mode
            self.notificationEntry(notification);
            self.notificationForm.addClass('absolute').show();
        }
        else {
            //create mode
            self.notificationEntry(jQuery.extend({}, self.emptyNotificationRow));
            self.notificationEntry().location_id = self.selectedLocationId();
        }
        
        var target = $( event.target );
        var row = $(target.parents('.records .row'));
        if (typeof(notification) == 'object' && row.length > 0) {
//            // edit mode: place form instead of original row 
            row.addClass('edited');
            self.notificationForm.css('top', Math.ceil(row.offset().top)+'px');
        }
        else {
            // place form in the end of forecast block
           self.notificationForm.removeClass('absolute').show();
        }
        
    };
    
    // process notification form
    self.updateNotification = function() { 
        //fetch values
        var notification = self.notificationEntry();
        
        // validate
        var errors = [];
        if (typeof(notification.email) == 'undefined' || !notification.email) {
            errors.push('Email not set');
        }
        
        if (typeof(notification.locationId) == 'undefined' || !notification.locationId) {
            errors.push('Location not set');
        }
        
        if ((typeof(notification.to_hot) == 'undefined' || !notification.to_hot) && (typeof(notification.to_cold) == 'undefined' || !notification.to_cold) &&
                (typeof(notification.strong_wind) == 'undefined' || !notification.strong_wind)){
            errors.push('At least one option should be checked.');
        }
        
        if (errors.length > 0) {
            alert('Validation error: '+errors.join('; '));
            
            return false;
        }
        
        if (self.notificationEntry().id > 0 ) {
            notification.id = self.notificationEntry().id;
        }
        else {
            delete notification.id;
        }
        
        $(self.notificationForm.find('[type="submit"]')).addClass('ajx-loading');
        
        // send
        $.ajax({
            type: 'POST',
            url: '/app_dev.php/forecast/notification',
            data: notification,
            success: function(data) {
                
                var needToDelete = data.hasOwnProperty('locationId') && data.locationId != self.selectedLocationId();
                
                if (notification.hasOwnProperty('id')) {
                   // update notifications
                    for (var i in self.notifications()) {
                        var row = (self.notifications())[i];
                        if (row.id == data.id) {
                            if (needToDelete){
                                // remove record with another location
                                self.notifications.splice(i, 1);
                            }
                            else {
                                self.notifications.splice(i, 1, data);
                            }    
                        }
                    }
                }
                else {
                    if (!needToDelete) {
                        // append notifications list
                        self.notifications.push(data);
                    }
                }
                
                //hide form
                self.closeNotificationForm();
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
        
        return false;
    };
    
    self.closeNotificationForm = function(){
        // hide form
        self.showNotificationForm(false);
        self.notificationForm.removeClass('absolute').hide(); 
        // show all hidden rows
        $('#notifications .records .row').removeClass('edited');
        $(self.notificationForm.find('[type="submit"]')).removeClass('ajx-loading');
    };
    
    // on select another location action
    self.selectedLocationId.subscribe(function(newValue) {
        
        if (typeof(newValue) != 'undefined' && self.chosenSectionId() == 'Forecast') {
            self.goToSLocation(newValue);
        }
        self.loadNotifications();
        
     });
    
    // handle setting of new Forecast value
    self.chosenForecast.subscribe(function(newValue) {
        self.emptyForecast(!newValue || typeof(newValue.weather) == 'undefined' || !newValue.weather.length);
    });
    
    // on select another location action
    self.notifications.subscribe(function(rows) {
        for (var r in rows ) {
            var newRow = rows[r];
            if (typeof(newRow) == 'object') {
                // set location property 
                if (newRow.hasOwnProperty('locationId') && newRow.locationId > 0) {
                    for (var i in self.availableLocations()) {
                        var row = (self.availableLocations())[i];
                        if (row.id == newRow.locationId) {
                            newRow.location = row;
                        }
                    }
                }
            }
        }
     });
    
    // prepare forecast record to display on the page
    self.extendForecast = function(newValue) {
            
        // prepare summary
        newValue.summary = {
            'img' : '',
            'temp': '',
            'tempMax': '',
            'tempMin': ''
        };

        // prepare weather rows & count temps
        var minTemp = null;
        var maxTemp = null;
        var avgTemp = 0;
        var avgRainChance = 0;
        var avgHumidity = 0;

        for (var i in newValue.weather) {
            var date = new Date(newValue.weather[i].timestamp*1000);
            newValue.weather[i].time = date.format(self.timeFormat);
            if (maxTemp === null || newValue.weather[i].temp > maxTemp ){
                maxTemp = newValue.weather[i].temp;
            }
            if (minTemp === null || newValue.weather[i].temp < minTemp ){
                minTemp = newValue.weather[i].temp;
            }
            avgTemp += newValue.weather[i].temp;
            avgRainChance += newValue.weather[i].rainChance;
        }

        if (newValue.weather.length > 0) {
            var i = newValue.weather.length;
            newValue.summary.temp = Math.round(avgTemp / i);
            avgRainChance = Math.round(avgRainChance / i);
            avgHumidity = Math.round(avgHumidity / i);
        }
        newValue.summary.tempMax = maxTemp;
        newValue.summary.tempMin = minTemp;
        newValue.summary.rainChance = avgRainChance;
        newValue.summary.humidity = avgHumidity;
        
        var imgClass = '';
        // define weather image
        if (avgRainChance >= 0 && avgRainChance < 30) {
            // sunny
            imgClass = 'icon-sunny';
        }
        else if(avgRainChance > 30 && avgRainChance < 60){
            // drizzle
            imgClass = 'icon-drizzle';
        }
        else if(avgRainChance > 60){
            // snow or rain
            if (avgTemp <= 5) {
                // snow
                imgClass = 'icon-snowy';
            }
            else {
                // rain
                imgClass = 'icon-rainy';
            }
        }
        newValue.summary.img = '<span class="'+imgClass+'"></span>';
        

        // sort weather rows by time
        newValue.weather.sort(function(a, b){
            if (a.timestamp > b.timestamp) {
                return 1;
            }
            else if (a.timestamp < b.timestamp) {
                return -1;
            }
            else {
                return 0;
            }
        });
        
        return newValue;
    };
    
    // change page location with forecast
    self.goToSLocation = function() {
        var dateTxt = self.chosenDate().format(self.dateFormat);
        var locationId = self.selectedLocationId();
        location.hash = '#Forecast' + '/' + locationId + '/' + dateTxt;
    }
    
    //navigate the dates
    self.goToNext = function() {
        var date = self.chosenDate();
        date.setDate(date.getDate()+1);
        self.chosenDate(date);
        return self.goToSLocation();
    }
    
    self.goToPrev = function() {
        var date = self.chosenDate();
        date.setDate(date.getDate()-1);
        self.chosenDate(date);
        return self.goToSLocation();
    }
    
    /**
     * Fetch forecast data from server side
     */
    self.loadForecast = function(locationId, date, forced){
        
        var dateTxt = date.format(self.dateFormat);
        var key = locationId+'__'+dateTxt;
        
        if (!forced && typeof(cacheData[key])!='undefined') {
            self.chosenForecast(cacheData[key]); 
        }
        else {
            if (typeof(cacheData[key])!='undefined') { 
                // remove cached forecast
                delete cacheData[key];
            }
            self.chosenForecast(null);
            $.ajax({
                type: 'GET',
                url: '/app_dev.php/forecast/',
                data: {
                    locationId: locationId, 
                    date: dateTxt
                },
                success: function(data) {

                    var formattedDate = self.chosenDate().format(self.dateFormat);
                    // save returned object
                    data = self.extendForecast(data);
                    if (data) {
                        cacheData[key] = data;
                        if (dateTxt === formattedDate) {
                            self.chosenForecast(data);    
                        }
                    }
                    else {
                        if (dateTxt === formattedDate) {
                            self.emptyForecast(true);
                        }
                    }
                },
                error: function(answer){
                    if (typeof(answer.responseJSON) != 'undefined') {
                        alert(answer.responseJSON.message);
                    }
                },
                dataType: 'json'
            });
        }    
    };
    
    self.notificationsXhr = null;
    self.loadNotifications = function(){
        
        if (self.notificationsXhr != null) {
            self.notificationsXhr.abort();
        }
        
        var locationId = self.selectedLocationId();
        self.notificationsXhr = $.ajax({
            type: 'GET',
            url: '/app_dev.php/forecast/notifications/'+locationId,
            success: function(data) {
                
                if (locationId == self.selectedLocationId()) {
                    // save returned objects
                    self.notifications(data);
                }
            },
            error: function(answer){
                if (typeof(answer.responseJSON) != 'undefined') {
                    alert(answer.responseJSON.message);
                }
            },
            dataType: 'json'
        });
        
    };
    
    // Client-side routes    
    Sammy(function() {
        this.get('#:section', function() {
            var section = this.params.section;
            
            self.chosenForecast(null);
            self.chosenSectionId(section);
            
            if (section == 'Forecast') {
                
                if (!self.chosenDate()) {
                    var date = new Date();
                    self.chosenDate(date);
                }
                
                self.goToSLocation();
            } 
            else if (section == 'Notifications'){
                
                // define the selected Location!
                if (!self.selectedLocationId() && self.availableLocations.length) {
                    // set first location
                    self.selectedLocationId(locations[0].id);
                }
            }
            else if (section == 'Check Date') {
                jQuery('#chosen-date').pickmeup({
                    format: self.dateInputJSFormat,
                    change: function(){
                        // handle setting of new chosen date txt value
                        if ($(this).pickmeup('get_date') != self.chosenDate()) {
                            self.chosenDate( $(this).pickmeup('get_date') );
                        }
                        $(this).pickmeup('hide');
                    }
                });
            }
        });

        this.get('#:section/:locationId/:date', function() {
            var section = this.params.section,
                locationId = this.params.locationId,
                date = this.params.date;
            
            
            self.chosenDate(new Date(date));
            self.selectedLocationId(locationId);
            self.chosenSectionId(section);
            
            self.loadForecast(self.selectedLocationId(), self.chosenDate(), false);
        });
        
        this.get('', function() { this.app.runRoute('get', '#Forecast') });
        
        // Override this function so that Sammy doesn't mess with forms
        this._checkFormSubmission = function(form) {
            return (false);
        };
        
        
    }).run();    
};


var cacheData = {};


jQuery(document).ready(function(){
    
    ko.applyBindings(new forecastViewModel());
    
});