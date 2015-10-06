
var guiders = function(t) {
    var e = {version: "1.1.1", _defaultSettings: {attachTo: null, buttons: [{name: "Close"}], buttonCustomHTML: "", description: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.", isHashable: !0, onShow: null, overlay: !1, position: 0, offset: {top: null, left: null}, title: "Sample title goes here", width: 400, xButton: !1}, _htmlSkeleton: ["<div class='outty'>", "	<div class='outty_shadow'></div>", "		<div class='outty_container'>", "			<div class='outty_content'>", "				<h2 class='outty_title'></h2>", "				<div class='outty_close'></div>", "				<p class='outty_description'></p>", "				<div class='outty_buttons'></div>", "			</div>", "		</div>", "	", "	<div class='outty_arrow'></div>", "</div>"].join(""), _arrowShortSize: 28, _arrowLongSize: 68, _guiders: {}, _currentGuiderID: null, _lastCreatedGuiderID: null, _addButtons: function(o) {
            for (var i = o.elem.find(".outty_buttons"), n = o.buttons.length - 1; n >= 0; n--) {
                var r = o.buttons[n], a = t('<a href="#"></a>').append(t("<span />").text(r.name));
                "undefined" != typeof r.classString && null !== r.classString && a.addClass(r.classString), i.append(a), r.onclick ? a.bind("click", r.onclick) : r.onclick || "close" !== r.name.toLowerCase() ? r.onclick || "next" !== r.name.toLowerCase() || a.bind("click", function() {
                    e.next()
                }) : a.bind("click", function() {
                    e.hideAll()
                })
            }
            if ("" !== o.buttonCustomHTML) {
                var d = t(o.buttonCustomHTML);
                o.elem.find(".outty_buttons").append(d)
            }
        }, _addXButton: function(o) {
            var i = o.elem.find(".outty_close"), n = t("<div></div>", {"class": "x_button", role: "button"});
            i.append(n), t('<a href="#" class="close-button-blue"></a>').appendTo(n).click(function(t) {
                e.hideAll(), t.stopPropagation(), t.preventDefault()
            }), n.click(function() {
                e.hideAll()
            })
        }, _attach: function(o) {
            if ("undefined" != typeof o.attachTo && null !== o) {
                var i = o.elem.innerHeight(), n = o.elem.innerWidth();
                if (0 === o.position)
                    return o.elem.css("position", "absolute"), o.elem.css("top", (t(window).height() - i) / 3 + t(window).scrollTop() + "px"), o.elem.css("left", (t(window).width() - n) / 2 + t(window).scrollLeft() + "px"), void 0;
                o.attachTo = t(o.attachTo);
                var r = o.attachTo.offset(), a = o.attachTo.innerHeight(), d = o.attachTo.innerWidth(), u = r.top, l = r.left, s = .9 * e._arrowShortSize, c = {1: [-s - i, d - n], 2: [0, s + d], 3: [a / 2 - i / 2, s + d], 4: [a - i, s + d], 5: [s + a, d - n], 6: [s + a, d / 2 - n / 2], 7: [s + a, 0], 8: [a - i, -n - s], 9: [a / 2 - i / 2, -n - s], 10: [0, -n - s], 11: [-s - i, 0], 12: [-s - i, d / 2 - n / 2]};
                offset = c[o.position], u += offset[0], l += offset[1], null !== o.offset.top && (u += o.offset.top), null !== o.offset.left && (l += o.offset.left), o.elem.css({position: "absolute", top: u, left: l})
            }
        }, _guiderById: function(t) {
            if ("undefined" == typeof e._guiders[t])
                throw"Cannot find guider with id " + t;
            return e._guiders[t]
        }, _showOverlay: function() {
            t("#outty_overlay").fadeIn("fast")
        }, _hideOverlay: function() {
            t("#outty_overlay").fadeOut("fast")
        }, _initializeOverlay: function() {
            0 === t("#outty_overlay").length && t('<div id="outty_overlay"></div>').hide().appendTo("body")
        }, _styleArrow: function(o) {
            var i = o.position || 0;
            if (i) {
                var n = t(o.elem.find(".outty_arrow")), r = {1: "outty_arrow_down", 2: "outty_arrow_left", 3: "outty_arrow_left", 4: "outty_arrow_left", 5: "outty_arrow_up", 6: "outty_arrow_up", 7: "outty_arrow_up", 8: "outty_arrow_right", 9: "outty_arrow_right", 10: "outty_arrow_right", 11: "outty_arrow_down", 12: "outty_arrow_down"};
                n.addClass(r[i]);
                var a = o.elem.innerHeight(), d = o.elem.innerWidth(), u = e._arrowLongSize / 2, l = {1: ["right", u], 2: ["top", u], 3: ["top", a / 2 - u], 4: ["bottom", u], 5: ["right", u], 6: ["left", d / 2 - u], 7: ["left", u], 8: ["bottom", u], 9: ["top", a / 2 - u], 10: ["top", u], 11: ["left", u], 12: ["left", d / 2 - u]}, i = l[o.position];
                n.css(i[0], i[1] + "px")
            }
        }, _showIfHashed: function(t) {
            var o = "guider=", i = window.location.hash.indexOf(o);
            if (-1 !== i) {
                var n = window.location.hash.substr(i + o.length);
                t.id.toLowerCase() === n.toLowerCase() && e.show(t.id)
            }
        }, next: function() {
            var t = e._guiders[e._currentGuiderID];
            if ("undefined" != typeof t) {
                var o = t.next || null;
                if (null !== o && "" !== o) {
                    var i = e._guiderById(o), n = i.overlay ? !0 : !1;
                    e.hideAll(n), e.show(o)
                }
            }
        }, createGuider: function(o) {
            (null === o || void 0 === o) && (o = {}), myGuider = t.extend({}, e._defaultSettings, o), myGuider.id = myGuider.id || String(Math.floor(1e3 * Math.random()));
            var i = t(e._htmlSkeleton);
            return myGuider.elem = i, myGuider.elem.css("width", myGuider.width + "px"), i.find(".outty_title").html(myGuider.title), i.find("p.outty_description").html(myGuider.description), e._addButtons(myGuider), myGuider.xButton && e._addXButton(myGuider), i.hide(), i.appendTo("body"), i.attr("id", myGuider.id), "undefined" != typeof myGuider.attachTo && null !== myGuider && (e._attach(myGuider), e._styleArrow(myGuider)), e._initializeOverlay(), e._guiders[myGuider.id] = myGuider, e._lastCreatedGuiderID = myGuider.id, myGuider.isHashable && e._showIfHashed(myGuider), e
        }, hideAll: function(o) {
            return t(".outty:not(.modal)").fadeOut("fast"), "undefined" != typeof o && o === !0 || e._hideOverlay(), e
        }, show: function(o) {
            !o && e._lastCreatedGuiderID && (o = e._lastCreatedGuiderID);
            var i = e._guiderById(o);
            i.overlay && e._showOverlay(), e._attach(i), i.onShow && i.onShow(i), i.elem.fadeIn("fast");
            var n = t(window).height(), r = t(window).scrollTop(), a = i.elem.offset(), d = i.elem.height();
            return(a.top - r < 0 || a.top + d + 40 > r + n) && window.scrollTo(0, Math.max(a.top + d / 2 - n / 2, 0)), e._currentGuiderID = o, e
        }};
    return e
}.call(this, jQuery);

