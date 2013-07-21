/*!
 * MessageBus - JavaScript page level Message Bus
 *
 * Version: 1.1
 * Released: 2010-05-20
 * Source: http://labs.appendto.com/javascript-messagebus
 * Author: Jonathan Sharp
 * License: MIT,GPL
 * 
 * Copyright (c) 2010 appendTo LLC.
 * Dual licensed under the MIT and GPL licenses.
 * http://appendto.com/open-source-licenses
 */
window.MessageBus = (function() {
	var _listeners = {}, _handlers = {}, _cache = {}, guid = 0;
	// Generate permutations on each topic
	function permutate(topic) {
		var topics = [topic.toLowerCase()];
		var t = topics[0].split('.');
		var tf = t[0], tr = t[t.length - 1];
		for ( var i = 1, l = t.length, x = l - 1; i < l; i++ ) {
			topics.push( tf + '.**' );
			if ( i+1 == l ) {
				topics.push( tf + '.*' );
				topics.push( '*.' + tr );
			}
			topics.push( '**.' + tr );
			tf += '.' + t[i];
			tr = t[x - i] + '.' + tr;
		}
		topics.push('**');
		if ( t.length == 1 ) {
			topics.push('*');
		}
		return topics;
	}
	function sanitize(topic) {
		return topic.toLowerCase().replace(/[^a-z0-9\.\*]/g, '');
	}
		
	return {
		handler: function(topic, callback) {
			if ( arguments.length == 1 ) {
				// We have an extended handler registration, so give it access 
				// to on subscribe, on publish messages
				t = arguments[0].topic = sanitize(arguments[0].topic);
				_handlers[t] = arguments[0];
			} else {
				topic = sanitize(topic);
				_handlers[topic] = {
					topic: topic,
					callback: function(next, data) {
						callback.call({}, function(ret) {
							next(ret);
						}, data);
					}
				};
			}
		},
		removeHandler: function(topic) {
			topic = sanitize(topic);
			if ( _handlers[topic] ) {
				try {
					if ( _handlers[topic].onRemove ) {
						_handlers[topic].onRemove(topic);
					}
					delete _handlers[topic];
				} catch(e) {};
			}
		},
		// Place a request on a topic for a response
		request: function(topic, data, fn) {
			var topics = permutate(topic), match = false, ret = undefined;
			for ( var i = 0, l = topics.length; i < l; i++ ) {
				var t = topics[i];
				if ( _handlers[t] ) {
					match = true;
					ret = _handlers[t].callback.call(_handlers[t], function(ret) {
						if ( fn ) {
							fn(ret, true);
						}
					}, data);
					if ( _handlers[t].publish !== false ) {
						this.publish(topic, data);
					}
					// Only execute the first handler
					return ret;
					break;
				}
			}
			if ( !match ) {
				// We have an error, there is no handler available
				if ( fn ) {
					fn(null, false);
				}
				this.publish(topic, data);
				return ret;
			}
		},
		subscribe: function(topic, args, callback) {
			if ( arguments.length == 2 ) {
				callback = args;
				args = {};
			}
			topic = sanitize(topic);
			if ( !_listeners[topic] ) {
				_listeners[topic] = [];
			}
			
			var topics = permutate(topic), l = topics.length;
			// Check if any topic handlers are going to prevent this topic from being subscribed
			for ( var i = 0; i < l; i++ ) {
				var t = topics[i];
				if ( _handlers[t] && _handlers[t].onSubscribe ) {
					if ( _handlers[t].onSubscribe.call(_handlers[t], topic, args, callback) === false ) {
						return false;
					}
				}
			}
			
			var id = args && args.id ? args.id : 'sid' + (guid++);
			_listeners[topic].push({
				topic: topic,
				id: id,
				args: args,
				callback: function(topic, data, id) {
					callback.call({
						listeningTopic: this.topic,
						topic: topic,
						id: this.id,
						args: this.args
					}, data, id);
				}
			});
			return id;
		},
		unsubscribe: function(topic, id) {
			topic = sanitize(topic);
			
			var topics = permutate(topic), l = topics.length;
			// Check if any topic handlers are going to prevent this topic from being unsubscribed
			for ( var i = 0; i < l; i++ ) {
				var t = topics[i];
				if ( _handlers[t] && _handlers[t].onUnsubscribe ) {
					if ( _handlers[t].onUnsubscribe.call(_handlers[t], topic, id) === false ) {
						return;
					}
				}
			}
			
			if ( _listeners[topic] ) {
				if ( id == '*' ) {
					try {
						delete _listeners[topic];
					} catch (e) {};
				} else {
					for ( var j = 0, k = _listeners[topic].length; j < k; j++ ) {
						if ( _listeners[topic][j] && _listeners[topic][j].id == id ) {
							_listeners[topic][j] = null;
						}
					}
				}
			}
		},
		publish: function(topic, data, id, retries) {
			if ( arguments.length == 2 ) {
				id = 'pid' + (guid++);
			}

            if (!retries) {
                retries = 0;
            }
			
			var topics = permutate(topic), l = topics.length;
			// Check if any topic handlers are going to prevent this topic from being published
			for ( var i = 0; i < l; i++ ) {
				var t = topics[i];
				if ( _handlers[t] && _handlers[t].onPublish ) {
					if ( _handlers[t].onPublish.call(_handlers[t], topic, data, id) === false ) {
						return;
					}
				}
			}
			
            var exec = 0;
			for ( var i = 0, l = topics.length; i < l; i++ ) {
				var t = topics[i];
				if ( _listeners[t] ) {
					for ( var j = 0, k = _listeners[t].length; j < k; j++ ) {
						if ( _listeners[t][j] !== null ) {
							var listener = _listeners[t][j];
							// Filter if the subscriber has the same id as the publisher
							if ( listener.args.id != id ) {
								try {
									listener.callback.call(listener, topic, data, id);
                                    exec++;
								} catch (e) { }; // TODO: Add debugging callback
							}
						}
					}
				}
			}

            if (exec==0 && retries++ < 100) {
                // exec failed, tried it again in 10 miliseconds
                var that = this;
                setTimeout(function() {
                    that.publish(topic, data, id, retries);
                }, 50);
            }
		}
	};
})();
