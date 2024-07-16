/*!
 * @license
 * chartjs-plugin-datalabels
 * http://chartjs.org/
 * Version: 0.4.0
 *
 * Copyright 2018 Chart.js Contributors
 * Released under the MIT license
 * https://github.com/chartjs/chartjs-plugin-datalabels/blob/master/LICENSE.md
 */
!function (t, e) { "object" == typeof exports && "undefined" != typeof module ? e(require("chart.js")) : "function" == typeof define && define.amd ? define(["chart.js"], e) : e(t.Chart) }(this, function (l) { "use strict"; var t = (l = l && l.hasOwnProperty("default") ? l.default : l).helpers, i = function () { this._rect = null, this._rotation = 0 }; t.extend(i.prototype, { update: function (t, e, n) { var a = t.x, r = t.y, i = a + e.x, o = r + e.y; this._rotation = n, this._rect = { x0: i - 1, y0: o - 1, x1: i + e.w + 2, y1: o + e.h + 2, cx: a, cy: r } }, contains: function (t, e) { var n, a, r, i, o, l = this._rect; return !!l && (n = l.cx, a = l.cy, r = this._rotation, i = n + (t - n) * Math.cos(r) + (e - a) * Math.sin(r), o = a - (t - n) * Math.sin(r) + (e - a) * Math.cos(r), !(i < l.x0 || o < l.y0 || i > l.x1 || o > l.y1)) } }); var r = l.helpers, e = "undefined" != typeof window ? window.devicePixelRatio : 1, v = { toTextLines: function (t) { var e, n = []; for (t = [].concat(t); t.length;)"string" == typeof (e = t.pop()) ? n.unshift.apply(n, e.split("\n")) : Array.isArray(e) ? t.push.apply(t, e) : r.isNullOrUndef(t) || n.unshift("" + e); return n }, toFontString: function (t) { return !t || r.isNullOrUndef(t.size) || r.isNullOrUndef(t.family) ? null : (t.style ? t.style + " " : "") + (t.weight ? t.weight + " " : "") + t.size + "px " + t.family }, textSize: function (t, e, n) { var a, r = [].concat(e), i = r.length, o = t.font, l = 0; for (t.font = n.string, a = 0; a < i; ++a)l = Math.max(t.measureText(r[a]).width, l); return t.font = o, { height: i * n.lineHeight, width: l } }, parseFont: function (t) { var e = l.defaults.global, n = r.valueOrDefault(t.size, e.defaultFontSize), a = { family: r.valueOrDefault(t.family, e.defaultFontFamily), lineHeight: r.options.toLineHeight(t.lineHeight, n), size: n, style: r.valueOrDefault(t.style, e.defaultFontStyle), weight: r.valueOrDefault(t.weight, null), string: "" }; return a.string = v.toFontString(a), a }, bound: function (t, e, n) { return Math.max(t, Math.min(e, n)) }, arrayDiff: function (t, e) { var n, a, r, i, o = t.slice(), l = []; for (n = 0, r = e.length; n < r; ++n)i = e[n], -1 === (a = o.indexOf(i)) ? l.push([i, 1]) : o.splice(a, 1); for (n = 0, r = o.length; n < r; ++n)l.push([o[n], -1]); return l }, rasterize: function (t) { return Math.round(t * e) / e } }; function u(t, e) { var n = e.x, a = e.y; if (null === n) return { x: 0, y: -1 }; if (null === a) return { x: 1, y: 0 }; var r = t.x - n, i = t.y - a, o = Math.sqrt(r * r + i * i); return { x: o ? r / o : 0, y: o ? i / o : -1 } } function f(t, e, n, a, r) { switch (r) { case "center": n = a = 0; break; case "bottom": n = 0, a = 1; break; case "right": n = 1, a = 0; break; case "left": n = -1, a = 0; break; case "top": n = 0, a = -1; break; case "start": n = -n, a = -a; break; case "end": break; default: r *= Math.PI / 180, n = Math.cos(r), a = Math.sin(r) }return { x: t, y: e, vx: n, vy: a } } var s = function (t, e, n) { var a, r = (t.startAngle + t.endAngle) / 2, i = Math.cos(r), o = Math.sin(r), l = t.innerRadius, s = t.outerRadius; return a = "start" === e ? l : "end" === e ? s : (l + s) / 2, f(t.x + i * a, t.y + o * a, i, o, n) }, d = function (t, e, n, a) { var r = u(t, a), i = t.radius, o = 0; return "start" === e ? o = -i : "end" === e && (o = i), f(t.x + r.x * o, t.y + r.y * o, r.x, r.y, n) }, c = function (t, e, n, a) { var r = t.horizontal, i = Math.abs(t.base - (r ? t.x : t.y)), o = r ? Math.min(t.x, t.base) : t.x, l = r ? t.y : Math.min(t.y, t.base), s = u(t, a); return "center" === e ? r ? o += i / 2 : l += i / 2 : "start" !== e || r ? "end" === e && r && (o += i) : l += i, f(o, l, s.x, s.y, n) }, h = function (t, e, n, a) { var r = u(t, a); return f(t.x, t.y, r.x, r.y, n) }, b = l.helpers, m = v.rasterize; var p = function (t, e, n, a) { var r = this; r._hitbox = new i, r._config = t, r._index = a, r._model = null, r._ctx = e, r._el = n }; b.extend(p.prototype, { _modelize: function (t, e, n) { var a, r = this._index, i = b.options.resolve, o = v.parseFont(i([e.font, {}], n, r)); return { align: i([e.align, "center"], n, r), anchor: i([e.anchor, "center"], n, r), backgroundColor: i([e.backgroundColor, null], n, r), borderColor: i([e.borderColor, null], n, r), borderRadius: i([e.borderRadius, 0], n, r), borderWidth: i([e.borderWidth, 0], n, r), clip: i([e.clip, !1], n, r), color: i([e.color, l.defaults.global.defaultFontColor], n, r), font: o, lines: t, offset: i([e.offset, 0], n, r), opacity: i([e.opacity, 1], n, r), origin: function (t) { var e = t._model.horizontal, n = t._scale || e && t._xScale || t._yScale; if (!n) return null; if (void 0 !== n.xCenter && void 0 !== n.yCenter) return { x: n.xCenter, y: n.yCenter }; var a = n.getBasePixel(); return e ? { x: a, y: null } : { x: null, y: a } }(this._el), padding: b.options.toPadding(i([e.padding, 0], n, r)), positioner: (a = this._el, a instanceof l.elements.Arc ? s : a instanceof l.elements.Point ? d : a instanceof l.elements.Rectangle ? c : h), rotation: i([e.rotation, 0], n, r) * (Math.PI / 180), size: v.textSize(this._ctx, t, o), textAlign: i([e.textAlign, "start"], n, r) } }, update: function (t) { var e, n, a, r = null, i = this._index, o = this._config; b.options.resolve([o.display, !0], t, i) && (e = t.dataset.data[i], n = b.valueOrDefault(b.callback(o.formatter, [e, t]), e), r = (a = b.isNullOrUndef(n) ? [] : v.toTextLines(n)).length ? this._modelize(a, o, t) : null), this._model = r }, draw: function (t) { var e, n, a, r, i, o, l, s, u, f, d, c, h, y, x, g = t.ctx, p = this._model; p && p.opacity && (r = p.size, i = p.padding, o = r.height, l = r.width, u = -o / 2, e = { frame: { x: (s = -l / 2) - i.left, y: u - i.top, w: l + i.width, h: o + i.height }, text: { x: s, y: u, w: l, h: o } }, n = function (t, e, n) { var a = e.positioner(t._view, e.anchor, e.align, e.origin), r = a.vx, i = a.vy; if (!r && !i) return { x: a.x, y: a.y }; var o = e.borderWidth || 0, l = n.w + 2 * o, s = n.h + 2 * o, u = e.rotation, f = Math.abs(l / 2 * Math.cos(u)) + Math.abs(s / 2 * Math.sin(u)), d = Math.abs(l / 2 * Math.sin(u)) + Math.abs(s / 2 * Math.cos(u)), c = 1 / Math.max(Math.abs(r), Math.abs(i)); return f *= r * c, d *= i * c, f += e.offset * r, d += e.offset * i, { x: a.x + f, y: a.y + d } }(this._el, p, e.frame), this._hitbox.update(n, e.frame, p.rotation), g.save(), p.clip && (a = t.chartArea, g.beginPath(), g.rect(a.left, a.top, a.right - a.left, a.bottom - a.top), g.clip()), g.globalAlpha = v.bound(0, p.opacity, 1), g.translate(m(n.x), m(n.y)), g.rotate(p.rotation), f = g, d = e.frame, h = (c = p).backgroundColor, y = c.borderColor, x = c.borderWidth, (h || y && x) && (f.beginPath(), b.canvas.roundedRect(f, m(d.x) - x / 2, m(d.y) - x / 2, m(d.w) + x, m(d.h) + x, c.borderRadius), f.closePath(), h && (f.fillStyle = h, f.fill()), y && x && (f.strokeStyle = y, f.lineWidth = x, f.lineJoin = "miter", f.stroke())), function (t, e, n, a) { var r, i, o, l = a.textAlign, s = a.font.lineHeight, u = a.color, f = e.length; if (f && u) for (r = n.x, i = n.y + s / 2, "center" === l ? r += n.w / 2 : "end" !== l && "right" !== l || (r += n.w), t.font = a.font.string, t.fillStyle = u, t.textAlign = l, t.textBaseline = "middle", o = 0; o < f; ++o)t.fillText(e[o], m(r), m(i), m(n.w)), i += s }(g, p.lines, e.text, p), g.restore()) }, contains: function (t, e) { return this._hitbox.contains(t, e) } }); var o = l.helpers, n = { align: "center", anchor: "center", backgroundColor: null, borderColor: null, borderRadius: 0, borderWidth: 0, clip: !1, color: void 0, display: !0, font: { family: void 0, lineHeight: 1.2, size: void 0, style: void 0, weight: null }, offset: 4, opacity: 1, padding: { top: 4, right: 4, bottom: 4, left: 4 }, rotation: 0, textAlign: "start", formatter: function (t) { if (o.isNullOrUndef(t)) return null; var e, n, a, r = t; if (o.isObject(t)) if (o.isNullOrUndef(t.label)) if (o.isNullOrUndef(t.r)) for (r = "", a = 0, n = (e = Object.keys(t)).length; a < n; ++a)r += (0 !== a ? ", " : "") + e[a] + ": " + t[e[a]]; else r = t.r; else r = t.label; return "" + r }, listeners: {} }, _ = l.helpers, w = "$datalabels"; function a(t, e) { var n, a, r = t.getDatasetMeta(e).data || [], i = r.length; for (n = 0; n < i; ++n)(a = r[n][w]) && a.draw(t) } function y(t, e, n) { var a, r, i, o, l = t[w].labels; for (a = l.length - 1; 0 <= a; --a)for (r = (i = l[a] || []).length - 1; 0 <= r; --r)if ((o = i[r]).contains(e, n)) return { dataset: a, label: o }; return null } function x(t, e, n) { var a = e && e[n.dataset]; if (a) { var r = n.label, i = r.$context; !0 === _.callback(a, [i]) && (t[w].dirty = !0, r.update(i)) } } function g(t, e) { var n, a, r = t[w], i = r.listeners; if (i.enter || i.leave) { if ("mousemove" === e.type) a = y(t, e.x, e.y); else if ("mouseout" !== e.type) return; var o, l, s, u, f, d; n = r.hovered, r.hovered = a, o = t, l = i, u = a, ((s = n) || u) && (s ? u ? s.label !== u.label && (d = f = !0) : d = !0 : f = !0, d && x(o, l.leave, s), f && x(o, l.enter, u)) } } l.defaults.global.plugins.datalabels = n, l.defaults.global.plugins.datalabels = n, l.plugins.register({ id: "datalabels", beforeInit: function (t) { t[w] = { actives: [] } }, beforeUpdate: function (t) { var e = t[w]; e.listened = !1, e.listeners = {}, e.labels = [] }, afterDatasetUpdate: function (t, a, e) { var n, r, i, o, l, s = a.index, u = t[w], f = u.labels[s] = [], d = t.isDatasetVisible(s), c = t.data.datasets[s], h = (n = e, !1 === (r = c.datalabels) ? null : (!0 === r && (r = {}), _.merge({}, [n, r]))), y = a.meta.data || [], x = y.length, g = t.ctx; for (g.save(), i = 0; i < x; ++i)o = y[i], d && o && !o.hidden && !o._model.skip ? (f.push(l = new p(h, g, o, i)), l.update(l.$context = { active: !1, chart: t, dataIndex: i, dataset: c, datasetIndex: s })) : l = null, o[w] = l; g.restore(), _.merge(u.listeners, h.listeners || {}, { merger: function (t, e, n) { e[t] = e[t] || {}, e[t][a.index] = n[t], u.listened = !0 } }) }, afterDatasetsDraw: function (t) { for (var e = 0, n = t.data.datasets.length; e < n; ++e)a(t, e) }, beforeEvent: function (t, e) { if (t[w].listened) switch (e.type) { case "mousemove": case "mouseout": g(t, e); break; case "click": a = e, r = (n = t)[w].listeners.click, (i = r && y(n, a.x, a.y)) && x(n, r, i) }var n, a, r, i }, afterEvent: function (t) { var e, n, a, r, i = t[w], o = i.actives, l = i.actives = t.lastActive || [], s = v.arrayDiff(o, l); for (e = 0, n = s.length; e < n; ++e)(a = s[e])[1] && (r = a[0][w]) && (r.$context.active = 1 === a[1], r.update(r.$context)); !i.dirty && !s.length || t.animating || t.render(), delete i.dirty } }) });