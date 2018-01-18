var AutoCSS = window.AutoCSS || null;

( function( $, api ) {

    AutoCSS = function(){
        this.values = {};
        this.lastValues = {};
        this.devices = [ 'desktop', 'tablet', 'mobile' ];
    };
    AutoCSS._change = false;
    AutoCSS.prototype.fonts = {};
    AutoCSS.prototype.styling_fields = {
        color: null,
        image: null,
        position: null,
        cover: null,
        repeat: null,
        attachment: null,

        border_width: null,
        border_color: null,
        border_style: null
    };
    AutoCSS.prototype.subsets = {};
    AutoCSS.prototype.variants = {};
    AutoCSS.prototype.media_queries = {
        all: '%s',
        desktop: '@media screen and (min-width: 64em) { %s }',
        tablet : '@media screen and (max-width: 64em) and (min-width: 35.5em) { %s }',
        mobile: '@media screen and (max-width: 35.5em) { %s }',
    };

    AutoCSS.prototype.css = {
        all: '',
        desktop: '',
        tablet : '',
        mobile: ''
    };

    AutoCSS.prototype.reset = function(){
        this.fonts = {};
        this.subsets = {};
        this.variants = {};
        this.css = {
            all: '',
            desktop: '',
            tablet : '',
            mobile: ''
        };
    };

    AutoCSS.prototype.encodeValue =function( value ){
        return encodeURI( JSON.stringify( value ) )
    };
    AutoCSS.prototype.decodeValue = function( value ){
        return JSON.parse( decodeURI( value ) );
    };

    AutoCSS.prototype.loop_fields = function( fields, values, skip_if_val_null, no_selector ){
        if ( _.isUndefined( skip_if_val_null ) ) {
            skip_if_val_null = false;
        }

        if ( ! _.isObject( values ) ) {
            values = {};
        }

        var that = this;

        var fields_code = {};

        _.each( fields, function( field ){
            var v =  ! _.isUndefined( values[ field.name ] ) ? values[ field.name ] : null;
            if ( !( _.isNull( v ) && skip_if_val_null ) ) {
                if (field.selector && field.css_format) {
                    switch (field.type) {
                        case 'css_ruler':
                            fields_code[ field.name ] = that.css_ruler(field, v, no_selector );
                            break;
                        case 'slider':
                            fields_code[ field.name ] = that.slider(field, v, no_selector );
                            break;
                        case 'color':
                            fields_code[ field.name ] = that.color(field, v, no_selector );
                            break;
                        case 'checkbox':
                            fields_code[ field.name ] = that.checkbox(field, v, no_selector );
                            break;
                        case 'image':
                            fields_code[ field.name ] = that.image( field, v, no_selector );
                            break;
                        case 'text_align':
                        case 'text_align_no_justify':
                            fields_code[ field.name ] = that.text_align(field, v, no_selector );
                            break;
                        case 'font':
                            fields_code[ field.name ] = that.font(field, v, no_selector );
                            break;
                        case 'styling':
                            fields_code[ field.name ] = that.styling( field, v, no_selector );
                            break;
                        default:
                            switch (field.css_format) {
                                case  'background':
                                case  'styling':
                                    fields_code[ field.name ] = that.styling( field, v, no_selector );
                                    break;
                                case 'typography':
                                    fields_code[ field.name ] = that.typography(field, v, no_selector );
                                    break;
                                case 'html_class':
                                    that.html_class(field, v );
                                    break;
                                default:
                                    fields_code[ field.name ] = that.maybe_devices_setup(field, 'setup_default', v, no_selector );
                            }
                    }
                } // end if selector and css format
            }

        } ); // end _.each

        return fields_code;
    };
    AutoCSS.prototype.run = function(){

        this.lastValues = this.values;
        this.values = api.get();

        if ( window.Customify_JS ) {
            if ( window.Customify_JS.css_media_queries ) {
                this.media_queries = window.Customify_JS.css_media_queries;
            }
        }

        this.reset();

       // console.log( 'NEW CUSTOMIZE VALUES', this.values );
        var that = this;
        that.loop_fields( Customify_Preview_Config.fields );

        var css_code = '';
        var i = 0;
        _.each( that.css, function( code, device ){
            var new_line = '';
            if ( i > 0 ) {
                new_line=  "\r\n\r\n\r\n\r\n\r";
            }
            css_code += new_line + that.media_queries[ device ].replace(/%s/g, code ) + "\r\n";
            i++;
        } );

        var url = that.get_google_fonts_url();
        if ( url ) {
            css_code = "\r\n@import url('"+url+"');\r\n\r\n"+css_code;
        }

        css_code = css_code.trim();
        if ( $( '#customify-style-inline-css' ).length <= 0 ) {
            $( 'head' ).append( "<style id='customify-style-inline-css' type='text/css'></style>" )
        }
        $( '#customify-style-inline-css' ).html( css_code );
       // api.set( 'customify__css',  css_code );
        $( document ).trigger( 'header_builder_panel_changed', [ 'auto_render_css' ] );

        //top.wp.customize('customify__css').set( css_code );
        //console.log( 'customify__css_Change', css_code );
    };


    AutoCSS.prototype.get_setting = function(name, device, key  ){
        if ( _.isUndefined( device ) ) {
            device = 'desktop';
        }
        if ( _.isUndefined( key  ) ) {
            key = false;
        }

        var get_value = null;
        var value;
        var df = false;
        if ( !_.isUndefined( Customify_Preview_Config.fields['setting|'+name ] ) ) {
            var field = Customify_Preview_Config.fields['setting|'+name ];
            df = !_.isUndefined( field.default ) ? field.default : false;
        }

        value = !_.isUndefined( this.values[ name ] ) ? this.values[ name ] : df;
        

        if ( _.isString( value ) ) {
            try {
                var decodeValue = this.decodeValue(value);
                if ( !_.isNull( decodeValue ) ) {
                    value = decodeValue;
                }
            } catch (e) {

            }
        }

        if ( ! key ) {
            if ( device !== 'all' ) {
                if ( _.isObject( value ) && !_.isUndefined( value[ device ] ) ) {
                    get_value =  value[ device ];
                }
            } else {
                get_value = value;
            }
        } else {
            var value_by_key = _.isUndefined( value[ key ] ) ?  value[ key ]: false;
            if ( device !== 'all' && _.isObject( value_by_key ) ) {
                if ( _.isObject( value_by_key ) && !_.isUndefined( value_by_key[ device ] ) ) {
                    get_value =  value_by_key[ device ];
                } else {
                    get_value =  value_by_key;
                }
            } else {
                get_value = value_by_key;
            }
        }

        return get_value;
    };

    AutoCSS.prototype.get_google_fonts_url = function(){
        var url = '//fonts.googleapis.com/css?family=';
        var s = '';
        var that = this;
        if ( _.isEmpty( that.fonts ) ) {
            return false;
        }
        _.each ( that.fonts, function( font_name ){
            if ( s ){
                s += '|';
            }
            s += font_name.replace(/\s/g, '+');
            var v = {};
            if ( !_.isUndefined( that.variants[ font_name ] ) ) {

                _.each( that.variants[ font_name ], function( _v ){
                    if ( _v !== 'regular' ) {
                        switch ( _v ) {
                            case 'italic':
                                v[_v] = '400i';
                                break;
                            default:
                                if ( _.isString( _v ) ) {
                                    v[_v] = _v.replace( 'italic', 'i');
                                } else {
                                    v[_v] = _v;
                                }

                        }
                    }
                } )
            }

            if ( ! _.isEmpty( v ) ) {
                s +=  ':'+that.join( v, ',' );
            }

        } );
        url += s;
        if ( ! _.isEmpty( that.subsets ) ) {
            url +='&subset='+that.join( that.subsets , ',' );
        }
        return url;
    };

    AutoCSS.prototype.join = function( object, glue ){

        if( _.isUndefined( glue ) ) {
            glue = '';
        }
        if( _.isArray( object ) ) {
            return object.join( glue );
        }

        if ( !_.isObject( object ) || _.isEmpty( object ) ) {
            return '';
        }

        var array = _.values( object );
        return array.join( glue );

    };

    AutoCSS.prototype.str_value = function( value, format ){
        if ( _.isEmpty( value ) ) {
            return '';
        }
        if ( ! _.isString( format ) ) {
            return '';
        }
        var find = '{{value}}';
        var reg = new RegExp(find, 'g');

        var s = format.replace( reg, value );

        var find_2 = '{{value_no_unit}}';
        var reg2 = new RegExp(find_2, 'g');
        s = s.replace( reg2, value );
        return s;
    };

    AutoCSS.prototype.setup_color = function( value, format ){
        if ( format ) {
            if ( value ) {
                return this.str_value( value, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.setup_checkbox = function( value, format ){
        if ( format ) {
            if ( value ) {
                return format;
            }
        }
        return false;
    };

    AutoCSS.prototype.setup_image = function( value, format ){
        var image = this.sanitize_media( value );
        if ( image.url ) {
            if ( format ) {
                return this.str_value( image.url, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.setup_slider = function ( value, format ){
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        value = _.defaults( value, {
            unit: 'px',
            value: null
        });

        if ( ! value.unit ) {
            value.unit = 'px';
        }

        var c = '';
        var v = '';

        if ( format ) {
            if ( value.value ) {
                v = value.value + value.unit;
                c = this.str_value( v, format );
                c = this.str_value( value.value, c );
            }
        }
        return c;
    };

    AutoCSS.prototype.setup_default = function( value, format ){
        if ( format ) {
            if ( value ) {
                return this.str_value( value, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.setup_css_ruler = function ( value, format ){
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        value = _.defaults( value, {
            unit : '',
            top: '',
            right: '',
            bottom: '',
            left: ''
        });

        if ( ! _.isUndefined( value.unit ) ) {
            value.unit = 'px';
        }

        format = _.defaults( format, {
            top: '',
            right: '',
            bottom: '',
            left: ''
        } );
        var that = this;

        var  code = {};
        _.each( format, function( string, pos ){
            var v = value[ pos ];
            if ( v && string ) {
                if ( string ) {
                    v = v + value['unit'];
                    code[ pos ] = that.str_value( v, string );
                }
            }
        } );

        return that.join( code, "\n\t" );
    };


    AutoCSS.prototype.setup_text_align = function( value, format ) {
        if ( format  ) {
            if ( value ) {
                return this.str_value( value, format );
            }
        }
        return false;
    };

    AutoCSS.prototype.sanitize_color = function ( color ){
       return color;
    };

    AutoCSS.prototype.sanitize_media = function ( value ) {
        if ( ! _.isObject( value ) ) {
            value = {};
        }
        return _.defaults( value, {
            id: null,
            url: null,
            mime: null
        } );
    };

    AutoCSS.prototype.maybe_devices_setup = function( field, call_back, values, no_selector ) {
        var code = '';
        var code_array = {};
        var has_device = false;
        var format = !_.isEmpty( field.css_format ) ? field.css_format : false;
        var that = this;

        if ( _.isUndefined( no_selector ) ) {
            no_selector = false;
        }

        var no_value = false;

        if ( _.isUndefined( values ) || _.isNull( values ) ) {
            values = {};
            no_value = true;
        }

        if ( ! _.isUndefined( field.device_settings ) && field.device_settings ) {
            has_device = true;
            _.each( that.devices, function( device ){
                var value = null;
                if ( no_value ) {
                    value = that.get_setting( field.name, device );
                } else {
                    if ( ! _.isUndefined( values[ device ] ) ) {
                        value = values[ device ];
                    }
                }

                var _c = false;
                if ( that[call_back] ){
                    _c = that[ call_back ]( value, format );
                }

                if ( _c ) {
                    code_array[ device ] = _c;
                }
            } );
        } else {
            if ( no_value ) {
                values = that.get_setting( field.name, 'all' );
            }
            if ( that[call_back] ){
                code = that[ call_back ]( values, format );
            }

            code_array.no_devices = code;
        }

        if ( _.isEmpty( code_array ) ) {
           // return false;
        }

        code = '';
        if ( no_selector ) {
            return code_array;
        } else {
            if ( has_device ) {
                _.each( that.devices, function( device ){
                    if ( !_.isUndefined( code_array[ device ] ) ) {
                        var _c = code_array[ device ];
                        if( _c ) {
                            that.css[ device ] += "\r\n"+field.selector+" {\r\n\t"+_c+"\r\n}\r\n" ;
                        }
                    }
                } );
            } else {
                if ( code_array.no_devices ) {
                    that.css.all += "\r\n"+field.selector+"  {\r\n\t"+code_array.no_devices+"\r\n}\r\n";
                }
            }
        }
        return code;
    };

    AutoCSS.prototype.setup_font = function ( value ){
        if( ! _.isObject( value ) ) {
            value = {};
        }
        value = _.defaults( value, {
            font: null,
            type: null,
            variant: null,
            subsets: null,
        });

        if ( ! value.font ) {
            return '';
        }

        if ( value.type == 'google' ){
            this.fonts[ value.font ] = value.font;
            if ( value.variant ) {
                if ( _.isUndefined( this.variants[ value.font ] ) ) {
                    this.variants[ value.font ] = {};
                    if ( _.isString( value.variant )  ) {
                        var vr;
                        vr ={};
                        vr[ value.variant ] =  value.variant;
                        this.variants[ value.font ] = _.extend( this.variants[ value.font ] , vr ) ;
                    } else {
                        this.variants[ value.font ] = _.extend( this.variants[ value.font ] , value.variant ) ;
                    }
                }
            }

            if ( value.subsets ) {
                this.subsets = _.extend( this.subsets, value.subsets ) ;
            }
        }

        return "font-family: \""+value.font+"\";";
    };

    AutoCSS.prototype.font = function( field, values ){
        var code = '';
        var that = this;
        if ( field.device_settings ) {

            _.each( this.devices, function( device ) {
                var value = null;
                if ( _.isEmpty( values ) ) {
                    value = that.get_setting( field.name, device );
                } else {
                    if ( !_.isUndefined( values[ device ] ) ) {
                        device = values[ device ];
                    }
                }
                var _c = that.setup_font( value );
                if ( _c ) {
                    that.css[ device ] = "\r\n"+field.selector+" {\r\n\t"+_c+"\r\n}\r\n";
                    if ( 'desktop' === device ) {
                        code += "\r\n"+field.selector+" {\r\n\t"+_c+"\r\n}";
                    } else {
                        code += "\r\n."+device+" "+field.selector+" {\r\n\t"+_c+"\r\n}\r\n";
                    }
                }
            } );

        } else {
            if ( _.isEmpty( values ) ) {
                values = that.get_setting( field.name );
            }
            code = that.setup_font( values );
            that.css[ 'all' ] += " "+field.selector+"  {\r\n\t"+code+"\r\n}\r\n";
            code += " "+field.selector+"  {\r\n\t"+code+"\r\n}\r\n";
        }

        return code;
    };


    AutoCSS.prototype.css_ruler = function( field, value, no_selector ){
        return this.maybe_devices_setup( field, 'setup_css_ruler', value, no_selector );
    };

    AutoCSS.prototype.slider = function( field, value, no_selector ){
        return this.maybe_devices_setup( field, 'setup_slider', value, no_selector );
    };

    AutoCSS.prototype.color = function( field, value, no_selector ){
        return this.maybe_devices_setup( field, 'setup_color', value, no_selector );
    };

    AutoCSS.prototype.checkbox = function( field, value, no_selector ){
        return this.maybe_devices_setup( field, 'setup_checkbox', value, no_selector );
    };

    AutoCSS.prototype.image = function( field, value, no_selector ){
        return this.maybe_devices_setup( field, 'setup_image', value, no_selector );
    };

    AutoCSS.prototype.text_align = function( field, value, no_selector ){
        return this.maybe_devices_setup( field, 'setup_text_align', value, no_selector );
    };

    AutoCSS.prototype.setup_styling_fields = function( fields, list, selectors, type ){
        var newfs;
        var i;
        var newList = [];
        if ( ! _.isObject( selectors ) ) {
            selectors = {};
        }

        if ( _.isUndefined( type ) ) {
            type = 'normal';
        }

        if ( fields === false ){
            newList = null;
        } else {
            if ( ! _.isObject( fields ) ) {
                fields = {};
            }
            newfs = {};
            i = 0;
            _.each( list, function( f ){
                if ( _.isUndefined( fields[ f.name ] ) || fields[ f.name ] ) {
                    newfs[ f.name ] = f;
                    if ( ! _.isUndefined( selectors[ type+'_'+f.name ] ) ) {
                        newfs[ f.name ]['selector'] = selectors[ type+'_'+f.name ];
                    } else {
                        newfs[ f.name ]['selector'] = selectors[ type ];
                    }
                    i++;
                }

            } );

            newList = newfs;

        }
        return newList;
    };

    AutoCSS.prototype.styling = function( field ){
        var that = this;
        // Setup file by default no need `css_format` key if filed have name in the list above
        var values = this.get_setting( field.name, 'all' );

        values = _.defaults( values, {
            'normal': {},
            'hover': {}
        } );
        var new_fields = {};
        var selectors = {};
        if ( _.isString( field.selector ) ) {
            selectors['normal'] = field.selector;
            selectors['hover'] = field.selector;
        } else {
            selectors = _.defaults( field.selector , {
                normal : null,
                hover : null
            } );
        }

        var tabs = null, normal_fields = -1, hover_fields = -1;

        if ( !_.isUndefined( field.fields ) && _.isObject(field.fields ) ) {
            if ( ! _.isUndefined( field.fields.tabs  ) ) {
                tabs = field.tabs;
            }
            if ( ! _.isUndefined( field.fields.normal_fields  ) ) {
                normal_fields =field.normal_fields;
            }

            if ( ! _.isUndefined( field.fields.hover_fields ) ) {
                hover_fields = field.hover_fields;
            }
        }

        var listNormalFields = that.setup_styling_fields( normal_fields,  Customify_Preview_Config.styling_config.normal_fields, selectors, 'normal' );
        var listHoverFields = that.setup_styling_fields( hover_fields,  Customify_Preview_Config.styling_config.hover_fields, selectors, 'hover' );

        var listTabs = _.clone( Customify_Preview_Config.styling_config.tabs );
        if ( tabs === false ) {
            listTabs['hover'] = false;
        } else if ( _.isObject( tabs ) ) {
            listTabs = tabs;
        }


        var _join = function( lists, codeList ){
            _.each( lists, function( f, name ){
                if ( _.isUndefined( selectorCSSAll[ f.selector ] ) ) {
                    selectorCSSAll[ f.selector ] = '';
                }

                if( !_.isUndefined( codeList[ name ] ) ) {
                    if ( ! _.isUndefined( codeList[ name ].no_devices ) ) {
                        if ( codeList[ name ].no_devices ) {
                            selectorCSSAll[ f.selector ] += codeList[ name ].no_devices;
                        }
                    } else {
                        _.each( codeList[ name ], function( code, device ) {

                            if ( _.isUndefined( selectorCSSDevices[ device ] ) ) {
                                selectorCSSDevices[ device ] = {};
                            }

                            if ( _.isUndefined( selectorCSSDevices[ device ][ f.selector ] ) ) {
                                selectorCSSDevices[ device ][ f.selector ] = '';
                            }

                            if ( code ) {
                                selectorCSSDevices[ device ][ f.selector ] += code;
                            }

                        } );
                    }
                }
            } );
        };

        var selectorCSSAll = {};
        var selectorCSSDevices = {};


        var normal_style = that.loop_fields(listNormalFields, values['normal'], true, true);
        var hover_style = that.loop_fields(listHoverFields, values['hover'], true, true);
        _join( listNormalFields, normal_style );
        _join( listHoverFields, hover_style );

        if ( field.name=== 'styling_new' ) {
            console.log('selectorCSSAll', selectorCSSAll);
            console.log('selectorCSSDevices', selectorCSSDevices);
        }

        _.each( selectorCSSAll, function( code, s ){
            that.css.all += "\r\n"+s+"  {\r\n\t"+code+"\r\n}\r\n";
        } );

        _.each( that.devices, function( device ){
            var css = '';
            if ( ! _.isUndefined( selectorCSSDevices[ device ] ) ) {
                var deviceCode = selectorCSSDevices[ device ];
                _.each( deviceCode, function( c, s ){
                 
                    if ( _.isString( c ) ) {
                        css += "\r\n"+s+"  {\r\n\t"+c+"\r\n}\r\n";
                    } else {
                        css += "\r\n"+s+"  {\r\n\t"+that.join( c, "\n" )+"\r\n}\r\n";
                    }
                } );
            }

            that.css[ device ] += css;
        } );

    };

    AutoCSS.prototype.setup_font_style = function ( value ){
        if ( ! _.isObject( value ) ) {
            value = {};
        }

        value = _.defaults( value, {
            b: null,
            i: null,
            u: null,
            s: null,
            t: null
        } );

        var css = {};
        if ( value['b'] ) {
            css['b'] = 'font-weight: bold;';
        }
        if ( value['i'] ) {
            css['i'] = 'font-style: italic;';
        }

        var decoration = {};
        if ( value['u'] ) {
            decoration['underline'] = 'underline';
        }

        if ( value['s'] ) {
            decoration['line-through'] = 'line-through';
        }

        if ( ! _.isEmpty( decoration ) ) {
            css['d'] = 'text-decoration: '+this.join( decoration, ' ' )+';';
        }

        if ( value['t'] ) {
            css['t'] = 'text-transform: uppercase;';
        }

        return this.join( css, "\r\n\t" );
    };

    AutoCSS.prototype.html_class = function( field ){

        var value = this.get_setting( field.name, 'all' );
        var that = this;
        var selector = field.selector;
        var last_value =  null;
        var is_checkbox = field.type === 'checkbox';
        if ( ! _.isUndefined( that.lastValues[ field.name ] ) ) {
            last_value = that.lastValues[ field.name ];
        }

        if ( _.isString( last_value ) ) {
            try {
                var decodeValue = that.decodeValue(last_value);
                if ( !_.isNull( decodeValue ) ) {
                    last_value = decodeValue;
                }
            } catch (e) {

            }
        }
        

        if ( _.isString( last_value ) && !_.isEmpty( last_value ) ) {
            $( selector ).removeClass( last_value );
        } else {
            if ( _.isObject( last_value ) ) {
                _.each( last_value, function( n, d ) {
                    if ( n ) {
                        var cl = d +'--' + n;
                        if ( is_checkbox ) {
                            cl = field.name+'-'+cl;
                        }
                        $( selector ).removeClass( cl );
                    }

                } );
            }
        }

        if ( _.isString( value ) ) {
            $( selector ).addClass( value );
        } else {
            if ( _.isObject( value ) ) {
                _.each( value, function( n, d ) {
                    if ( n ) {
                        var cl = d +'--' + n;
                        if ( is_checkbox ) {
                            cl = field.name+'-'+cl;
                        }
                        $( selector ).addClass( cl );
                    }

                } );
            }
        }

    };

    AutoCSS.prototype.typography = function( field, values ){
        if ( _.isUndefined( values ) || ! _.isObject( values ) ) {
            values = this.get_setting( field.name, 'all' );
        }

        var that = this;
        if ( ! _.isObject( values ) ) {
            values = {};
        }
        values = _.defaults( values, {
            font: null,
            font_type: null,
            languages: null,
            font_size: null,
            font_weight: null,
            line_height: null,
            letter_spacing: null,
            style: null,
            text_decoration: null,
            text_transform: null,
        });

        var code = {};
        var fields = {};
        var devices_css = {};
        _.each( Customify_Preview_Config.typo_fields, function( f ){
            fields[ f.name ] = f;
        } );

        if ( ! _.isUndefined( fields.font ) ) {
            code.font = this.setup_font( {
                font: values.font,
                type: values.font_type,
                subsets: values.languages,
                variant: values.font_weight,
            } );
        }

        // Font Style
        if ( ! _.isUndefined( fields.style ) ) {
            //code.font_style = this.setup_font_style( values.font_style );
            if ( values.style && values.style !== 'default' ) {
                code.style = 'font-style: '+values.style+';';
            }
        }

        // Font Weight
        if ( ! _.isUndefined( fields.font_weight ) ) {
            if ( values.font_weight && values.font_weight !== 'default' && values.font_weight !== 'default'  ) {
                code.font_weight = 'font-weight: '+values.font_weight+';';
            }
        }

        // Text Decoration
        if ( ! _.isUndefined( fields.text_decoration ) ) {
            if ( values.text_decoration && values.text_decoration !== 'default' ) {
                code.text_decoration = 'text-decoration: '+values.text_decoration+';';
            }
        }

        // Text Transform
        if ( ! _.isUndefined( fields.text_transform ) ) {
            if ( values.text_transform && values.text_transform !== 'default' ) {
                code.text_transform = 'text-transform: '+values.text_transform+';';
            }
        }

        if ( ! _.isUndefined( fields.font_size ) ) {
            fields.font_size.css_format = 'font-size: {{value}};';
            var font_size_css = this.maybe_devices_setup( fields.font_size, 'setup_slider', values.font_size, true );
            if ( !_.isEmpty( font_size_css ) ) {
                if ( ! _.isUndefined( font_size_css.no_devices ) ) {
                    code.font_size = font_size_css.no_devices;
                } else {
                    _.each( font_size_css, function( _c, device ){
                        if ( device == 'desktop' ) {
                            code.font_size = _c;
                        } else {
                            if ( _.isUndefined( devices_css[ device ] )  ) {
                                devices_css[ device ] = {};
                            }
                            devices_css[ device ]['font_size'] = _c;
                        }
                    } );
                }
            }
        }

        if ( !_.isUndefined( fields.line_height ) ) {
            fields.line_height['css_format'] = 'line-height: {{value}};';
            var line_height_css = this.maybe_devices_setup( fields.line_height , 'setup_slider', values['line_height'], true);
            if ( ! _.isEmpty( line_height_css ) ) {
                if ( ! _.isUndefined( line_height_css['no_devices'] ) ) {
                    code['line_height'] = line_height_css['no_devices'];
                } else {
                    _.each( line_height_css, function( _c, device ) {
                        if ( device == 'desktop' ) {
                            code['line_height'] = _c;
                        } else {
                            if ( _.isUndefined( devices_css[ device ] ) ) {
                                devices_css[ device ] = {};
                            }
                            devices_css[ device ]['line_height'] = _c;
                        }
                    } );
                }
            }
        }

        if (  !_.isUndefined( fields.letter_spacing ) ) {
            fields['letter_spacing']['css_format'] = 'letter-spacing: {{value}};';
            var letter_spacing_cs = this.maybe_devices_setup(fields['letter_spacing'], 'setup_slider', values['letter_spacing'], true);
            if (letter_spacing_cs) {
                if ( !_.isUndefined( letter_spacing_cs['no_devices'] ) ) {
                    code['letter_spacing'] = letter_spacing_cs['no_devices'];
                } else {
                    _.each( letter_spacing_cs, function( _c, device ){
                        if ( device == 'desktop' ) {
                            code['letter_spacing'] = _c;
                        } else {
                            if ( _.isUndefined( devices_css[ device ] ) ) {
                                devices_css[ device ] = {};
                            }
                            devices_css[ device ]['letter_spacing'] = _c;
                        }
                    } );
                }
            }
        }

        _.each( devices_css, function( els, device ){
            that.css[device] += " "+field['selector']+" {\r\n\t"+that.join( els, "\r\n\t" )+"\r\n}";
        } );

        that.css['all'] += " "+field['selector']+" {\r\n\t"+that.join( code, "\r\n\t" )+"\r\n}";
    };

    var AutoCSSInit = new AutoCSS();

    api.bind( 'preview-ready', function() {
        //AutoCSSInit.run();
        AutoCSSInit.lastValues = api.get();
        AutoCSSInit.values = api.get();
    });

    api.bind( 'change', function(){
       // AutoCSSInit.run();
    } );

    _.each( Customify_Preview_Config.fields, function( field ){
        if ( field.selector && field.css_format ) {
            // Header text color.
            wp.customize( field.name, function( setting ) {
                setting.bind( function( to ) {
                    AutoCSSInit.run();
                } );
            } );

        }
    });


} )( jQuery, wp.customize );