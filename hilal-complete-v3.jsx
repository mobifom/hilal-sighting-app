import { useState } from "react";

const C = {
  bg: "#0C1425",
  bg2: "#131E36",
  card: "#19274A",
  gold: "#D4A843",
  goldDim: "rgba(212,168,67,0.12)",
  text: "#ECE6D8",
  muted: "#7E8FAB",
  dark: "#4E5F7E",
  bdr: "#243052",
  ok: "#48B07A",
  warn: "#E2A33E",
  no: "#D45555",
  // Light theme for public portal
  pBg: "#FAFAF6",
  pCard: "#FFFFFF",
  pText: "#1C2537",
  pMuted: "#6B7A90",
  pBdr: "#E2E6ED",
  pGold: "#B8922E",
  pGoldLight: "#FDF6E3",
  pGoldBdr: "#E8D5A0",
};

const Badge = ({ s }) => {
  const c = { approved: C.ok, pending: C.warn, rejected: C.no };
  return <span style={{ padding: "3px 10px", borderRadius: 20, fontSize: 10, fontWeight: 700, background: c[s] + "22", color: c[s], textTransform: "uppercase" }}>{s}</span>;
};

const Dot = ({ c }) => <span style={{ width: 8, height: 8, borderRadius: "50%", background: c, display: "inline-block", flexShrink: 0 }} />;

const hijri = [
  { n: "Muharram", a: "مُحَرَّم", d: 30, g: "Jul 6, 2025" },
  { n: "Safar", a: "صَفَر", d: 29, g: "Aug 5, 2025" },
  { n: "Rabi al-Awwal", a: "رَبِيع الأَوَّل", d: 30, g: "Sep 3, 2025" },
  { n: "Rabi al-Thani", a: "رَبِيع الثَّانِي", d: 29, g: "Oct 3, 2025" },
  { n: "Jumada al-Ula", a: "جُمَادَىٰ الأُولَىٰ", d: 30, g: "Nov 1, 2025" },
  { n: "Jumada al-Thani", a: "جُمَادَىٰ الثَّانِيَة", d: 29, g: "Dec 1, 2025" },
  { n: "Rajab", a: "رَجَب", d: 30, g: "Dec 30, 2025" },
  { n: "Sha'ban", a: "شَعْبَان", d: 29, g: "Jan 29, 2026" },
  { n: "Ramadan", a: "رَمَضَان", d: 30, g: "Feb 27, 2026" },
  { n: "Shawwal", a: "شَوَّال", d: 29, g: "Mar 29, 2026" },
  { n: "Dhul Qi'dah", a: "ذُو القِعْدَة", d: 30, g: "Apr 27, 2026" },
  { n: "Dhul Hijjah", a: "ذُو الحِجَّة", d: 29, g: "May 27, 2026" },
];

const sights = [
  { who: "Sheikh Ahmad", loc: "Makkah, SA", date: "Feb 26", month: "Ramadan", status: "approved", photo: true },
  { who: "Imam Yusuf", loc: "Cairo, EG", date: "Feb 26", month: "Ramadan", status: "pending", photo: true },
  { who: "Dr. Hassan", loc: "Istanbul, TR", date: "Feb 26", month: "Ramadan", status: "pending", photo: false },
  { who: "Sheikh Omar", loc: "Riyadh, SA", date: "Feb 26", month: "Ramadan", status: "rejected", photo: true },
  { who: "Imam Ali", loc: "Kuala Lumpur, MY", date: "Feb 26", month: "Ramadan", status: "approved", photo: true },
];

const anns = [
  { t: "Ramadan 1447 Begins", ta: "بداية رمضان ١٤٤٧", date: "Feb 27, 2026", type: "month_start", pri: "high", body: "The crescent moon for Ramadan 1447 has been sighted. The first day of Ramadan will be Thursday, February 27, 2026. May Allah bless this sacred month." },
  { t: "Sha'ban Moon Sighted", ta: "رؤية هلال شعبان", date: "Jan 29, 2026", type: "sighting", pri: "high", body: "The crescent moon of Sha'ban has been confirmed by multiple observers across the Middle East region." },
  { t: "Night of Isra & Mi'raj", ta: "ليلة الإسراء والمعراج", date: "Jan 16, 2026", type: "event", pri: "medium", body: "The blessed night of Isra and Mi'raj falls on 27th Rajab. Muslims are encouraged to increase worship and prayers." },
  { t: "Mid-Sha'ban Night", ta: "ليلة النصف من شعبان", date: "Feb 12, 2026", type: "event", pri: "medium", body: "The night of mid-Sha'ban is a time of spiritual reflection. It is recommended to fast and perform extra prayers." },
  { t: "Fasting in Sha'ban", ta: "فضل صيام شعبان", date: "Feb 1, 2026", type: "general", pri: "low", body: "The Prophet (PBUH) used to fast most of Sha'ban. It is recommended to increase voluntary fasting this month." },
];

const priC = { high: C.no, medium: C.warn, low: C.ok };
const inp = { width: "100%", background: C.bg, border: `1px solid ${C.bdr}`, borderRadius: 8, padding: "10px 14px", color: C.text, fontSize: 13, boxSizing: "border-box", outline: "none" };
const lbl = { color: C.muted, fontSize: 10, display: "block", marginBottom: 5, textTransform: "uppercase", letterSpacing: .5, fontWeight: 600 };
const pInp = { width: "100%", background: "#fff", border: `1px solid ${C.pBdr}`, borderRadius: 10, padding: "12px 16px", color: C.pText, fontSize: 14, boxSizing: "border-box", outline: "none" };
const pLbl = { color: C.pMuted, fontSize: 11, display: "block", marginBottom: 6, textTransform: "uppercase", letterSpacing: .5, fontWeight: 600 };

/* ================================================================ */
/* =================== PUBLIC PORTAL SCREENS ====================== */
/* ================================================================ */

function PNav({ tab, setTab }) {
  return (
    <div style={{ background: "#fff", borderBottom: `1px solid ${C.pBdr}`, padding: "0 32px", display: "flex", alignItems: "center", justifyContent: "space-between" }}>
      <div style={{ display: "flex", alignItems: "center", gap: 24 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 8, padding: "14px 0" }}>
          <span style={{ fontSize: 24 }}>🌙</span>
          <span style={{ color: C.pGold, fontSize: 20, fontWeight: 800 }}>Hilal</span>
        </div>
        <div style={{ display: "flex", gap: 0 }}>
          {[
            { id: "home", l: "Home" },
            { id: "calendar", l: "Calendar" },
            { id: "announcements", l: "Announcements" },
            { id: "report", l: "Report Sighting" },
            { id: "about", l: "About" },
          ].map(t => (
            <button key={t.id} onClick={() => setTab(t.id)} style={{
              background: "none", border: "none", borderBottom: tab === t.id ? `2px solid ${C.pGold}` : "2px solid transparent",
              color: tab === t.id ? C.pGold : C.pMuted, padding: "16px 14px", fontSize: 13, fontWeight: tab === t.id ? 700 : 500, cursor: "pointer"
            }}>{t.l}</button>
          ))}
        </div>
      </div>
      <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
        <button style={{ background: "transparent", color: C.pMuted, border: `1px solid ${C.pBdr}`, borderRadius: 8, padding: "7px 14px", fontSize: 12, cursor: "pointer" }}>العربية</button>
        <button style={{ background: C.pGold, color: "#fff", border: "none", borderRadius: 8, padding: "7px 16px", fontSize: 12, fontWeight: 700, cursor: "pointer" }}>Sign In</button>
      </div>
    </div>
  );
}

function PHome() {
  return (
    <div>
      {/* Hero */}
      <div style={{ background: "linear-gradient(135deg, #1C2537 0%, #0C1425 50%, #19274A 100%)", padding: "60px 32px", textAlign: "center", position: "relative", overflow: "hidden" }}>
        <div style={{ position: "absolute", top: 20, left: "10%", fontSize: 60, opacity: .06 }}>🌙</div>
        <div style={{ position: "absolute", bottom: 10, right: "15%", fontSize: 80, opacity: .04 }}>☪</div>
        <div style={{ position: "relative", zIndex: 1 }}>
          <div style={{ fontSize: 48 }}>🌙</div>
          <div style={{ color: C.gold, fontSize: 52, fontWeight: 800, marginTop: 8, lineHeight: 1.1 }}>8 Sha'ban 1447</div>
          <div style={{ color: "#fff", fontSize: 24, marginTop: 4, opacity: .7 }}>٨ شعبان ١٤٤٧ هـ</div>
          <div style={{ color: C.muted, fontSize: 14, marginTop: 8 }}>Thursday, February 12, 2026</div>

          {/* Countdown */}
          <div style={{ display: "inline-flex", gap: 28, marginTop: 28, background: "rgba(212,168,67,0.1)", borderRadius: 16, padding: "18px 36px", border: "1px solid rgba(212,168,67,0.2)" }}>
            <div style={{ textAlign: "center" }}>
              <div style={{ color: C.gold, fontSize: 36, fontWeight: 800 }}>15</div>
              <div style={{ color: C.muted, fontSize: 11, textTransform: "uppercase", letterSpacing: 1 }}>Days</div>
            </div>
            <div style={{ width: 1, background: "rgba(212,168,67,0.2)" }} />
            <div style={{ textAlign: "center" }}>
              <div style={{ color: C.gold, fontSize: 36, fontWeight: 800 }}>8</div>
              <div style={{ color: C.muted, fontSize: 11, textTransform: "uppercase", letterSpacing: 1 }}>Hours</div>
            </div>
            <div style={{ width: 1, background: "rgba(212,168,67,0.2)" }} />
            <div style={{ textAlign: "center" }}>
              <div style={{ color: C.gold, fontSize: 36, fontWeight: 800 }}>23</div>
              <div style={{ color: C.muted, fontSize: 11, textTransform: "uppercase", letterSpacing: 1 }}>Minutes</div>
            </div>
          </div>
          <div style={{ color: C.gold, fontSize: 13, marginTop: 12, fontWeight: 600, letterSpacing: .5 }}>UNTIL RAMADAN 1447</div>
        </div>
      </div>

      {/* Latest Announcement Banner */}
      <div style={{ background: C.pGoldLight, borderBottom: `1px solid ${C.pGoldBdr}`, padding: "16px 32px", display: "flex", alignItems: "center", gap: 14 }}>
        <span style={{ background: C.pGold, color: "#fff", fontSize: 10, fontWeight: 800, padding: "3px 10px", borderRadius: 20, textTransform: "uppercase" }}>New</span>
        <div style={{ flex: 1 }}>
          <span style={{ color: C.pText, fontSize: 14, fontWeight: 600 }}>Mid-Sha'ban Night — </span>
          <span style={{ color: C.pMuted, fontSize: 13 }}>The blessed night falls on February 12. Recommended to fast and perform extra prayers.</span>
        </div>
        <span style={{ color: C.pMuted, fontSize: 12 }}>ليلة النصف من شعبان</span>
      </div>

      {/* Content */}
      <div style={{ maxWidth: 1000, margin: "0 auto", padding: "32px 24px" }}>
        {/* Quick Links */}
        <div style={{ display: "flex", gap: 16, marginBottom: 36, flexWrap: "wrap" }}>
          {[
            { ico: "📅", t: "Hijri Calendar", d: "View the full 1447 AH calendar with confirmed dates", color: "#E8F4FD", bdr: "#B8D8ED" },
            { ico: "📢", t: "Announcements", d: "Latest month confirmations and Islamic events", color: C.pGoldLight, bdr: C.pGoldBdr },
            { ico: "🔭", t: "Report Sighting", d: "Submit a moon sighting observation for verification", color: "#E8FDF0", bdr: "#A8DFC0" },
            { ico: "🌍", t: "Regional Dates", d: "Check moon sighting dates for your region", color: "#F3E8FD", bdr: "#D0B8ED" },
          ].map((c, i) => (
            <div key={i} style={{ flex: 1, minWidth: 200, background: c.color, borderRadius: 14, padding: "22px 20px", border: `1px solid ${c.bdr}`, cursor: "pointer" }}>
              <div style={{ fontSize: 28, marginBottom: 10 }}>{c.ico}</div>
              <div style={{ color: C.pText, fontSize: 15, fontWeight: 700 }}>{c.t}</div>
              <div style={{ color: C.pMuted, fontSize: 12, marginTop: 4, lineHeight: 1.5 }}>{c.d}</div>
            </div>
          ))}
        </div>

        {/* Two columns */}
        <div style={{ display: "flex", gap: 24, flexWrap: "wrap" }}>
          {/* Upcoming Months */}
          <div style={{ flex: 1, minWidth: 300, background: "#fff", borderRadius: 14, padding: 24, border: `1px solid ${C.pBdr}` }}>
            <div style={{ color: C.pGold, fontSize: 12, fontWeight: 700, textTransform: "uppercase", letterSpacing: .5, marginBottom: 16 }}>Upcoming Months</div>
            {hijri.slice(7, 12).map((m, i) => (
              <div key={i} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "12px 0", borderBottom: `1px solid ${C.pBdr}` }}>
                <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
                  <div style={{ width: 30, height: 30, borderRadius: "50%", background: i === 0 ? C.pGoldLight : "#F4F5F7", display: "flex", alignItems: "center", justifyContent: "center", color: i === 0 ? C.pGold : C.pMuted, fontSize: 11, fontWeight: 700 }}>{i + 8}</div>
                  <div>
                    <div style={{ color: C.pText, fontSize: 14, fontWeight: 600 }}>{m.n}</div>
                    <div style={{ color: C.pMuted, fontSize: 13 }}>{m.a}</div>
                  </div>
                </div>
                <div>
                  <div style={{ color: C.pText, fontSize: 13, textAlign: "right" }}>{m.g}</div>
                  <div style={{ color: i < 1 ? C.ok : C.pMuted, fontSize: 10, textAlign: "right", marginTop: 2 }}>{i < 1 ? "✓ Confirmed" : "Calculated"}</div>
                </div>
              </div>
            ))}
          </div>

          {/* Recent Announcements */}
          <div style={{ flex: 1, minWidth: 300, background: "#fff", borderRadius: 14, padding: 24, border: `1px solid ${C.pBdr}` }}>
            <div style={{ color: C.pGold, fontSize: 12, fontWeight: 700, textTransform: "uppercase", letterSpacing: .5, marginBottom: 16 }}>Recent Announcements</div>
            {anns.slice(0, 4).map((a, i) => (
              <div key={i} style={{ padding: "12px 0", borderBottom: `1px solid ${C.pBdr}` }}>
                <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 4 }}>
                  <Dot c={priC[a.pri]} />
                  <span style={{ color: C.pText, fontSize: 14, fontWeight: 600 }}>{a.t}</span>
                </div>
                <div style={{ color: C.pMuted, fontSize: 13, marginBottom: 2 }}>{a.ta}</div>
                <div style={{ color: C.pMuted, fontSize: 11 }}>{a.date}</div>
              </div>
            ))}
          </div>
        </div>

        {/* Subscribe */}
        <div style={{ marginTop: 36, background: "linear-gradient(135deg, #1C2537, #19274A)", borderRadius: 16, padding: "36px 32px", textAlign: "center" }}>
          <div style={{ fontSize: 28 }}>🔔</div>
          <div style={{ color: "#fff", fontSize: 20, fontWeight: 700, marginTop: 8 }}>Never Miss a Moon Sighting</div>
          <div style={{ color: C.muted, fontSize: 13, marginTop: 6 }}>Subscribe to get instant push notifications for new month confirmations</div>
          <div style={{ display: "flex", justifyContent: "center", gap: 10, marginTop: 20 }}>
            <input placeholder="Enter your email" style={{ width: 300, background: "rgba(255,255,255,0.08)", border: "1px solid rgba(255,255,255,0.15)", borderRadius: 10, padding: "12px 16px", color: "#fff", fontSize: 13, outline: "none", boxSizing: "border-box" }} />
            <button style={{ background: C.gold, color: C.bg, border: "none", borderRadius: 10, padding: "12px 24px", fontSize: 13, fontWeight: 700, cursor: "pointer" }}>Subscribe</button>
          </div>
          <div style={{ display: "flex", justifyContent: "center", gap: 16, marginTop: 16 }}>
            <button style={{ background: "rgba(255,255,255,0.08)", color: "#fff", border: "1px solid rgba(255,255,255,0.15)", borderRadius: 8, padding: "8px 16px", fontSize: 12, cursor: "pointer" }}>📱 Get the App</button>
            <button style={{ background: "rgba(255,255,255,0.08)", color: "#fff", border: "1px solid rgba(255,255,255,0.15)", borderRadius: 8, padding: "8px 16px", fontSize: 12, cursor: "pointer" }}>📧 Email Alerts</button>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div style={{ background: "#f0f0ec", borderTop: `1px solid ${C.pBdr}`, padding: "24px 32px", display: "flex", justifyContent: "space-between", alignItems: "center", flexWrap: "wrap", gap: 12 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
          <span style={{ fontSize: 16 }}>🌙</span>
          <span style={{ color: C.pGold, fontSize: 14, fontWeight: 700 }}>Hilal</span>
          <span style={{ color: C.pMuted, fontSize: 11 }}>Moon Sighting Platform</span>
        </div>
        <div style={{ display: "flex", gap: 20, fontSize: 12, color: C.pMuted }}>
          <span>Home</span><span>Calendar</span><span>Announcements</span><span>About</span><span>Privacy</span>
        </div>
        <div style={{ color: C.pMuted, fontSize: 11 }}>© 2026 Hilal. Based on Umm al-Qura Calendar</div>
      </div>
    </div>
  );
}

function PCalendar() {
  return (
    <div style={{ maxWidth: 960, margin: "0 auto", padding: "32px 24px" }}>
      <div style={{ textAlign: "center", marginBottom: 32 }}>
        <h1 style={{ color: C.pText, fontSize: 28, fontWeight: 800, margin: "0 0 6px" }}>Hijri Calendar — 1447 AH</h1>
        <p style={{ color: C.pMuted, fontSize: 14, margin: 0 }}>التقويم الهجري ١٤٤٧ — Complete year with Gregorian dates</p>
      </div>
      <div style={{ display: "flex", flexWrap: "wrap", gap: 16, justifyContent: "center" }}>
        {hijri.map((m, i) => {
          const cur = i === 7;
          const past = i < 7;
          return (
            <div key={i} style={{
              width: "calc(33% - 12px)", minWidth: 240, background: cur ? C.pGoldLight : "#fff",
              borderRadius: 14, padding: 20, border: `1px solid ${cur ? C.pGoldBdr : C.pBdr}`,
              boxSizing: "border-box", position: "relative"
            }}>
              {cur && <div style={{ position: "absolute", top: 10, right: 12, background: C.pGold, color: "#fff", fontSize: 9, fontWeight: 800, padding: "2px 10px", borderRadius: 10, textTransform: "uppercase" }}>Current Month</div>}
              <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 12 }}>
                <div style={{ width: 40, height: 40, borderRadius: "50%", background: cur ? C.pGold : "#F4F5F7", display: "flex", alignItems: "center", justifyContent: "center", color: cur ? "#fff" : C.pMuted, fontSize: 15, fontWeight: 800 }}>{i + 1}</div>
                <div>
                  <div style={{ color: C.pText, fontSize: 16, fontWeight: 700 }}>{m.n}</div>
                  <div style={{ color: C.pMuted, fontSize: 15 }}>{m.a}</div>
                </div>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", paddingTop: 12, borderTop: `1px solid ${cur ? C.pGoldBdr : C.pBdr}` }}>
                <div>
                  <div style={{ color: C.pMuted, fontSize: 10, textTransform: "uppercase", letterSpacing: .5 }}>Starts</div>
                  <div style={{ color: C.pText, fontSize: 14, fontWeight: 600, marginTop: 2 }}>{m.g}</div>
                </div>
                <div style={{ textAlign: "right" }}>
                  <div style={{ color: C.pMuted, fontSize: 10, textTransform: "uppercase", letterSpacing: .5 }}>Duration</div>
                  <div style={{ color: C.pText, fontSize: 14, fontWeight: 600, marginTop: 2 }}>{m.d} days</div>
                </div>
              </div>
              <div style={{ marginTop: 10, color: past ? C.ok : C.pMuted, fontSize: 11, fontWeight: 600 }}>
                {past ? "✓ Confirmed by sighting" : "📊 Calculated (Umm al-Qura)"}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

function PAnn() {
  return (
    <div style={{ maxWidth: 800, margin: "0 auto", padding: "32px 24px" }}>
      <div style={{ textAlign: "center", marginBottom: 32 }}>
        <h1 style={{ color: C.pText, fontSize: 28, fontWeight: 800, margin: "0 0 6px" }}>Announcements</h1>
        <p style={{ color: C.pMuted, fontSize: 14, margin: 0 }}>الإعلانات — Official moon sighting confirmations and Islamic events</p>
      </div>

      {/* Filter tabs */}
      <div style={{ display: "flex", gap: 8, marginBottom: 24, justifyContent: "center" }}>
        {["All", "Month Start", "Sighting", "Event", "General"].map((f, i) => (
          <button key={i} style={{
            background: i === 0 ? C.pGold : "transparent", color: i === 0 ? "#fff" : C.pMuted,
            border: `1px solid ${i === 0 ? C.pGold : C.pBdr}`, borderRadius: 20, padding: "6px 16px", fontSize: 12, fontWeight: 600, cursor: "pointer"
          }}>{f}</button>
        ))}
      </div>

      {anns.map((a, i) => (
        <div key={i} style={{ background: "#fff", borderRadius: 14, padding: "22px 24px", border: `1px solid ${C.pBdr}`, marginBottom: 14 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 10, flexWrap: "wrap" }}>
            <Dot c={priC[a.pri]} />
            <span style={{ padding: "2px 10px", borderRadius: 20, fontSize: 10, background: C.pGoldLight, color: C.pGold, fontWeight: 700, textTransform: "uppercase" }}>{a.type.replace("_", " ")}</span>
            <span style={{ color: C.pMuted, fontSize: 12, marginLeft: "auto" }}>{a.date}</span>
          </div>
          <div style={{ color: C.pText, fontSize: 18, fontWeight: 700 }}>{a.t}</div>
          <div style={{ color: C.pMuted, fontSize: 16, marginTop: 2 }}>{a.ta}</div>
          <div style={{ color: C.pMuted, fontSize: 13, marginTop: 10, lineHeight: 1.7 }}>{a.body}</div>
          <div style={{ display: "flex", gap: 12, marginTop: 14 }}>
            <button style={{ background: "#F4F5F7", color: C.pMuted, border: "none", borderRadius: 8, padding: "6px 14px", fontSize: 11, cursor: "pointer" }}>🔗 Share</button>
            <button style={{ background: "#F4F5F7", color: C.pMuted, border: "none", borderRadius: 8, padding: "6px 14px", fontSize: 11, cursor: "pointer" }}>🖨️ Print</button>
          </div>
        </div>
      ))}
    </div>
  );
}

function PReport() {
  return (
    <div style={{ maxWidth: 640, margin: "0 auto", padding: "32px 24px" }}>
      <div style={{ textAlign: "center", marginBottom: 32 }}>
        <div style={{ fontSize: 40 }}>🔭</div>
        <h1 style={{ color: C.pText, fontSize: 28, fontWeight: 800, margin: "8px 0 6px" }}>Report Moon Sighting</h1>
        <p style={{ color: C.pMuted, fontSize: 14, margin: 0 }}>تقرير رؤية الهلال — Submit your observation for scholar verification</p>
      </div>
      <div style={{ background: "#fff", borderRadius: 16, padding: 28, border: `1px solid ${C.pBdr}` }}>
        <div style={{ display: "flex", gap: 16, flexWrap: "wrap", marginBottom: 18 }}>
          <div style={{ flex: 1, minWidth: 200 }}><label style={pLbl}>Full Name</label><input placeholder="Your full name" style={pInp} /></div>
          <div style={{ flex: 1, minWidth: 200 }}><label style={pLbl}>Email</label><input placeholder="your@email.com" style={pInp} /></div>
        </div>
        <div style={{ marginBottom: 18 }}>
          <label style={pLbl}>For Hijri Month</label>
          <select style={pInp}><option>Ramadan 1447</option><option>Shawwal 1447</option></select>
        </div>
        <div style={{ display: "flex", gap: 16, flexWrap: "wrap", marginBottom: 18 }}>
          <div style={{ flex: 1, minWidth: 200 }}>
            <label style={pLbl}>Location</label>
            <div style={{ display: "flex", gap: 8 }}>
              <input placeholder="City, Country" style={{ ...pInp, flex: 1 }} />
              <button style={{ background: C.pGoldLight, color: C.pGold, border: `1px solid ${C.pGoldBdr}`, borderRadius: 10, padding: "0 14px", cursor: "pointer", fontSize: 13 }}>📍 GPS</button>
            </div>
          </div>
          <div style={{ flex: 1, minWidth: 200 }}><label style={pLbl}>Observation Date & Time</label><input placeholder="Feb 26, 2026 — 6:45 PM" style={pInp} /></div>
        </div>
        <div style={{ marginBottom: 18 }}>
          <label style={pLbl}>Photo Evidence</label>
          <div style={{ background: "#FAFAF6", border: `2px dashed ${C.pBdr}`, borderRadius: 14, padding: "32px 0", textAlign: "center", cursor: "pointer" }}>
            <div style={{ fontSize: 32 }}>📷</div>
            <div style={{ color: C.pMuted, fontSize: 13, marginTop: 8 }}>Click to upload or drag & drop</div>
            <div style={{ color: C.pMuted, fontSize: 11, marginTop: 4 }}>JPG, PNG up to 10MB</div>
          </div>
        </div>
        <div style={{ display: "flex", gap: 16, flexWrap: "wrap", marginBottom: 18 }}>
          <div style={{ flex: 1, minWidth: 200 }}>
            <label style={pLbl}>Sky Conditions</label>
            <select style={pInp}><option>Clear</option><option>Partly Cloudy</option><option>Cloudy</option><option>Hazy</option></select>
          </div>
          <div style={{ flex: 1, minWidth: 200 }}>
            <label style={pLbl}>Visibility Method</label>
            <select style={pInp}><option>Naked Eye</option><option>Binoculars</option><option>Telescope</option></select>
          </div>
        </div>
        <div style={{ marginBottom: 22 }}>
          <label style={pLbl}>Additional Notes</label>
          <textarea rows={3} placeholder="Describe what you saw, duration, direction, any witnesses..." style={{ ...pInp, resize: "vertical" }} />
        </div>
        <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 22 }}>
          <div style={{ width: 18, height: 18, borderRadius: 4, border: `2px solid ${C.pGold}`, background: C.pGoldLight, display: "flex", alignItems: "center", justifyContent: "center", color: C.pGold, fontSize: 12 }}>✓</div>
          <span style={{ color: C.pMuted, fontSize: 12 }}>I confirm this is an honest observation and I take responsibility for its accuracy</span>
        </div>
        <button style={{ width: "100%", background: C.pGold, color: "#fff", border: "none", borderRadius: 12, padding: "14px 0", fontSize: 16, fontWeight: 700, cursor: "pointer" }}>Submit Sighting Report</button>
        <div style={{ color: C.pMuted, fontSize: 11, textAlign: "center", marginTop: 12 }}>Your report will be reviewed by qualified scholars before confirmation</div>
      </div>
    </div>
  );
}

function PAbout() {
  return (
    <div style={{ maxWidth: 720, margin: "0 auto", padding: "32px 24px" }}>
      <div style={{ textAlign: "center", marginBottom: 36 }}>
        <div style={{ fontSize: 48 }}>🌙</div>
        <h1 style={{ color: C.pText, fontSize: 28, fontWeight: 800, margin: "8px 0 6px" }}>About Hilal</h1>
        <p style={{ color: C.pMuted, fontSize: 14, margin: 0 }}>عن هلال — Serving the Muslim community worldwide</p>
      </div>
      <div style={{ background: "#fff", borderRadius: 14, padding: 28, border: `1px solid ${C.pBdr}`, marginBottom: 20 }}>
        <h3 style={{ color: C.pGold, fontSize: 14, fontWeight: 700, textTransform: "uppercase", letterSpacing: .5, marginBottom: 12 }}>Our Mission</h3>
        <p style={{ color: C.pMuted, fontSize: 14, lineHeight: 1.8, margin: 0 }}>Hilal is a community-driven platform dedicated to accurate Islamic month determination through verified moon sighting reports. We combine traditional scholarly verification with modern technology to serve Muslims worldwide with reliable Hijri calendar information.</p>
      </div>
      <div style={{ display: "flex", gap: 16, flexWrap: "wrap", marginBottom: 20 }}>
        {[
          { ico: "🔭", t: "Verified Sightings", d: "Every report is reviewed by qualified Islamic scholars before confirmation" },
          { ico: "🌍", t: "Global Coverage", d: "Observers and scholars from across the world participate in our network" },
          { ico: "📲", t: "Instant Alerts", d: "Get push notifications the moment a new month is confirmed" },
        ].map((c, i) => (
          <div key={i} style={{ flex: 1, minWidth: 180, background: "#fff", borderRadius: 14, padding: 20, border: `1px solid ${C.pBdr}`, textAlign: "center" }}>
            <div style={{ fontSize: 28 }}>{c.ico}</div>
            <div style={{ color: C.pText, fontSize: 14, fontWeight: 700, marginTop: 8 }}>{c.t}</div>
            <div style={{ color: C.pMuted, fontSize: 12, marginTop: 6, lineHeight: 1.5 }}>{c.d}</div>
          </div>
        ))}
      </div>
      <div style={{ background: "#fff", borderRadius: 14, padding: 28, border: `1px solid ${C.pBdr}` }}>
        <h3 style={{ color: C.pGold, fontSize: 14, fontWeight: 700, textTransform: "uppercase", letterSpacing: .5, marginBottom: 12 }}>Contact & Get Involved</h3>
        <p style={{ color: C.pMuted, fontSize: 13, lineHeight: 1.8, margin: "0 0 16px" }}>Join our growing network of moon sighting observers. Register as a verified observer to submit reports and help the community.</p>
        <div style={{ display: "flex", gap: 10 }}>
          <button style={{ background: C.pGold, color: "#fff", border: "none", borderRadius: 8, padding: "10px 20px", fontSize: 13, fontWeight: 700, cursor: "pointer" }}>Register as Observer</button>
          <button style={{ background: "#F4F5F7", color: C.pMuted, border: `1px solid ${C.pBdr}`, borderRadius: 8, padding: "10px 20px", fontSize: 13, cursor: "pointer" }}>Contact Us</button>
        </div>
      </div>
    </div>
  );
}

/* ================================================================ */
/* =================== ADMIN PORTAL SCREENS ======================= */
/* ================================================================ */

function ADash() {
  return (
    <div>
      <h2 style={{ color: C.text, fontSize: 20, fontWeight: 700, margin: "0 0 4px" }}>Dashboard</h2>
      <p style={{ color: C.muted, fontSize: 12, margin: "0 0 20px" }}>Hijri Year 1447 AH — Overview</p>
      <div style={{ display: "flex", gap: 14, marginBottom: 22, flexWrap: "wrap" }}>
        {[
          { l: "Pending Sightings", v: "12", c: C.warn },
          { l: "Announcements Sent", v: "24", c: C.gold },
          { l: "Registered Observers", v: "156", c: C.ok },
          { l: "Current Month", v: "Sha'ban", c: C.gold },
        ].map((s, i) => (
          <div key={i} style={{ background: C.card, borderRadius: 10, padding: "16px 18px", border: `1px solid ${C.bdr}`, minWidth: 140, flex: 1 }}>
            <Dot c={s.c} />
            <div style={{ fontSize: 24, fontWeight: 700, color: C.text, marginTop: 8 }}>{s.v}</div>
            <div style={{ fontSize: 11, color: C.muted, marginTop: 3 }}>{s.l}</div>
          </div>
        ))}
      </div>
      <div style={{ display: "flex", gap: 16, flexWrap: "wrap" }}>
        <div style={{ flex: 1, minWidth: 280, background: C.card, borderRadius: 10, padding: 18, border: `1px solid ${C.bdr}` }}>
          <div style={{ color: C.gold, fontSize: 12, fontWeight: 700, marginBottom: 14, textTransform: "uppercase", letterSpacing: .5 }}>Recent Sighting Reports</div>
          {sights.slice(0, 4).map((r, i) => (
            <div key={i} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "9px 0", borderBottom: `1px solid ${C.bdr}` }}>
              <div>
                <div style={{ color: C.text, fontSize: 13, fontWeight: 500 }}>{r.who}</div>
                <div style={{ color: C.dark, fontSize: 11 }}>{r.loc} · {r.date}</div>
              </div>
              <Badge s={r.status} />
            </div>
          ))}
        </div>
        <div style={{ flex: 1, minWidth: 280, background: C.card, borderRadius: 10, padding: 18, border: `1px solid ${C.bdr}` }}>
          <div style={{ color: C.gold, fontSize: 12, fontWeight: 700, marginBottom: 14, textTransform: "uppercase", letterSpacing: .5 }}>Upcoming Months</div>
          {hijri.slice(7, 12).map((m, i) => (
            <div key={i} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "9px 0", borderBottom: `1px solid ${C.bdr}` }}>
              <div>
                <div style={{ color: C.text, fontSize: 13, fontWeight: 500 }}>{m.n}</div>
                <div style={{ color: C.muted, fontSize: 12 }}>{m.a}</div>
              </div>
              <div style={{ color: C.dark, fontSize: 12 }}>{m.g}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

function ASight() {
  return (
    <div>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20, flexWrap: "wrap", gap: 10 }}>
        <div>
          <h2 style={{ color: C.text, fontSize: 20, fontWeight: 700, margin: 0 }}>Moon Sighting Reports</h2>
          <p style={{ color: C.muted, fontSize: 12, margin: "3px 0 0" }}>Review and approve observer submissions</p>
        </div>
        <div style={{ display: "flex", gap: 8 }}>
          <select style={{ ...inp, width: "auto", padding: "7px 12px", fontSize: 12 }}><option>All Status</option><option>Pending</option><option>Approved</option></select>
          <select style={{ ...inp, width: "auto", padding: "7px 12px", fontSize: 12 }}><option>All Regions</option><option>Middle East</option><option>Asia</option></select>
        </div>
      </div>
      <div style={{ background: C.card, borderRadius: 10, border: `1px solid ${C.bdr}`, overflowX: "auto" }}>
        <table style={{ width: "100%", borderCollapse: "collapse", minWidth: 700 }}>
          <thead>
            <tr style={{ borderBottom: `2px solid ${C.bdr}` }}>
              {["Observer", "Location", "Month", "Date", "Photo", "Status", "Actions"].map(h => (
                <th key={h} style={{ padding: "12px 14px", textAlign: "left", color: C.muted, fontSize: 10, fontWeight: 700, textTransform: "uppercase", letterSpacing: .5 }}>{h}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {sights.map((r, i) => (
              <tr key={i} style={{ borderBottom: `1px solid ${C.bdr}` }}>
                <td style={{ padding: "11px 14px", color: C.text, fontSize: 13, fontWeight: 500 }}>{r.who}</td>
                <td style={{ padding: "11px 14px", color: C.muted, fontSize: 12 }}>{r.loc}</td>
                <td style={{ padding: "11px 14px", color: C.gold, fontSize: 12, fontWeight: 600 }}>{r.month}</td>
                <td style={{ padding: "11px 14px", color: C.muted, fontSize: 12 }}>{r.date}</td>
                <td style={{ padding: "11px 14px", fontSize: 12 }}>{r.photo ? <span style={{ color: C.ok }}>📷 Yes</span> : <span style={{ color: C.dark }}>—</span>}</td>
                <td style={{ padding: "11px 14px" }}><Badge s={r.status} /></td>
                <td style={{ padding: "11px 14px" }}>
                  <div style={{ display: "flex", gap: 5 }}>
                    <button style={{ background: C.ok + "22", color: C.ok, border: "none", borderRadius: 5, padding: "4px 10px", cursor: "pointer", fontSize: 11, fontWeight: 600 }}>✓</button>
                    <button style={{ background: C.no + "22", color: C.no, border: "none", borderRadius: 5, padding: "4px 10px", cursor: "pointer", fontSize: 11, fontWeight: 600 }}>✕</button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function AAnn() {
  return (
    <div>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 20 }}>
        <div>
          <h2 style={{ color: C.text, fontSize: 20, fontWeight: 700, margin: 0 }}>Announcements</h2>
          <p style={{ color: C.muted, fontSize: 12, margin: "3px 0 0" }}>Manage bilingual announcements</p>
        </div>
        <button style={{ background: C.gold, color: C.bg, border: "none", borderRadius: 8, padding: "9px 20px", fontSize: 12, fontWeight: 700, cursor: "pointer" }}>+ New</button>
      </div>
      {anns.map((a, i) => (
        <div key={i} style={{ background: C.card, borderRadius: 10, padding: "14px 18px", border: `1px solid ${C.bdr}`, marginBottom: 10, display: "flex", justifyContent: "space-between", alignItems: "center", flexWrap: "wrap", gap: 8 }}>
          <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
            <Dot c={priC[a.pri]} />
            <div>
              <div style={{ color: C.text, fontSize: 13, fontWeight: 600 }}>{a.t}</div>
              <div style={{ color: C.muted, fontSize: 13 }}>{a.ta}</div>
            </div>
          </div>
          <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
            <span style={{ color: C.dark, fontSize: 11 }}>{a.date}</span>
            <button style={{ background: C.goldDim, color: C.gold, border: "none", borderRadius: 6, padding: "4px 12px", fontSize: 11, cursor: "pointer", fontWeight: 600 }}>Edit</button>
            <button style={{ background: C.goldDim, color: C.gold, border: `1px solid ${C.gold}33`, borderRadius: 6, padding: "4px 12px", fontSize: 11, cursor: "pointer", fontWeight: 600 }}>📤 Push</button>
          </div>
        </div>
      ))}
    </div>
  );
}

function ACal() {
  return (
    <div>
      <h2 style={{ color: C.text, fontSize: 20, fontWeight: 700, margin: "0 0 4px" }}>Hijri Calendar — 1447 AH</h2>
      <p style={{ color: C.muted, fontSize: 12, margin: "0 0 20px" }}>Manage month start dates</p>
      <div style={{ display: "flex", flexWrap: "wrap", gap: 14 }}>
        {hijri.map((m, i) => {
          const cur = i === 7;
          const past = i < 7;
          return (
            <div key={i} style={{ background: cur ? C.goldDim : C.card, borderRadius: 10, padding: 16, border: `1px solid ${cur ? C.gold : C.bdr}`, width: "calc(33.33% - 10px)", minWidth: 200, boxSizing: "border-box", position: "relative" }}>
              {cur && <div style={{ position: "absolute", top: 8, right: 10, background: C.gold, color: C.bg, fontSize: 9, fontWeight: 800, padding: "2px 8px", borderRadius: 10 }}>CURRENT</div>}
              <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 10 }}>
                <div style={{ width: 34, height: 34, borderRadius: "50%", background: cur ? C.gold : C.bg, display: "flex", alignItems: "center", justifyContent: "center", color: cur ? C.bg : C.muted, fontSize: 13, fontWeight: 700 }}>{i + 1}</div>
                <div>
                  <div style={{ color: C.text, fontSize: 14, fontWeight: 600 }}>{m.n}</div>
                  <div style={{ color: C.muted, fontSize: 13 }}>{m.a}</div>
                </div>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", paddingTop: 10, borderTop: `1px solid ${C.bdr}` }}>
                <div><div style={{ color: C.dark, fontSize: 9, textTransform: "uppercase" }}>Starts</div><div style={{ color: C.text, fontSize: 12, marginTop: 2 }}>{m.g}</div></div>
                <div style={{ textAlign: "right" }}><div style={{ color: C.dark, fontSize: 9, textTransform: "uppercase" }}>Days</div><div style={{ color: C.text, fontSize: 12, marginTop: 2 }}>{m.d}</div></div>
              </div>
              <div style={{ display: "flex", gap: 6, marginTop: 10 }}>
                {past ? <span style={{ color: C.ok, fontSize: 11, fontWeight: 600 }}>✓ Confirmed</span> : (
                  <>
                    <button style={{ flex: 1, background: C.bg, color: C.muted, border: `1px solid ${C.bdr}`, borderRadius: 6, padding: "5px 0", fontSize: 10, cursor: "pointer" }}>Edit</button>
                    <button style={{ flex: 1, background: C.goldDim, color: C.gold, border: `1px solid ${C.gold}33`, borderRadius: 6, padding: "5px 0", fontSize: 10, cursor: "pointer", fontWeight: 600 }}>Confirm</button>
                  </>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

/* ================================================================ */
/* =================== MOBILE SCREENS ============================= */
/* ================================================================ */

function MHome() {
  return (
    <div>
      <div style={{ background: `linear-gradient(180deg, ${C.card} 0%, ${C.bg} 100%)`, padding: "22px 18px 18px", textAlign: "center" }}>
        <div style={{ fontSize: 40 }}>🌙</div>
        <div style={{ color: C.gold, fontSize: 22, fontWeight: 700, marginTop: 4 }}>8 Sha'ban</div>
        <div style={{ color: C.muted, fontSize: 18, marginTop: 2 }}>٨ شعبان ١٤٤٧</div>
        <div style={{ color: C.dark, fontSize: 11, marginTop: 3 }}>Thursday, February 12, 2026</div>
      </div>
      <div style={{ margin: "0 14px 14px", background: C.goldDim, borderRadius: 14, padding: 16, border: `1px solid ${C.gold}33`, textAlign: "center" }}>
        <div style={{ color: C.gold, fontSize: 10, textTransform: "uppercase", letterSpacing: 1, fontWeight: 700 }}>Ramadan Begins In</div>
        <div style={{ display: "flex", justifyContent: "center", gap: 20, marginTop: 10 }}>
          {[{ v: "15", l: "Days" }, { v: "8", l: "Hours" }, { v: "23", l: "Min" }].map((t, i) => (
            <div key={i}><div style={{ color: C.gold, fontSize: 26, fontWeight: 700 }}>{t.v}</div><div style={{ color: C.muted, fontSize: 9, textTransform: "uppercase" }}>{t.l}</div></div>
          ))}
        </div>
      </div>
      <div style={{ margin: "0 14px 14px", background: C.card, borderRadius: 12, padding: 14, border: `1px solid ${C.bdr}` }}>
        <div style={{ color: C.gold, fontSize: 10, fontWeight: 700, marginBottom: 8, textTransform: "uppercase" }}>📢 Latest Announcement</div>
        <div style={{ color: C.text, fontSize: 14, fontWeight: 600 }}>Mid-Sha'ban Night</div>
        <div style={{ color: C.muted, fontSize: 13, marginTop: 1 }}>ليلة النصف من شعبان</div>
        <div style={{ color: C.dark, fontSize: 11, marginTop: 6, lineHeight: 1.5 }}>The blessed night falls on Feb 12.</div>
      </div>
      <div style={{ display: "flex", flexWrap: "wrap", gap: 10, padding: "0 14px 20px" }}>
        {[{ icon: "📅", label: "Calendar" }, { icon: "🔭", label: "Report" }, { icon: "📢", label: "News" }, { icon: "🌙", label: "Moon" }].map((a, i) => (
          <div key={i} style={{ background: C.card, borderRadius: 12, padding: 14, border: `1px solid ${C.bdr}`, width: "calc(50% - 5px)", boxSizing: "border-box" }}>
            <div style={{ fontSize: 20, marginBottom: 6 }}>{a.icon}</div>
            <div style={{ color: C.text, fontSize: 12, fontWeight: 600 }}>{a.label}</div>
          </div>
        ))}
      </div>
    </div>
  );
}

function MCal() {
  return (
    <div style={{ padding: 14 }}>
      <div style={{ color: C.text, fontSize: 17, fontWeight: 700, marginBottom: 14 }}>Hijri Calendar 1447</div>
      {hijri.map((m, i) => {
        const cur = i === 7;
        return (
          <div key={i} style={{ display: "flex", alignItems: "center", gap: 10, padding: "10px 12px", marginBottom: 7, background: cur ? C.goldDim : C.card, borderRadius: 10, border: `1px solid ${cur ? C.gold + "55" : C.bdr}` }}>
            <div style={{ width: 28, height: 28, borderRadius: "50%", background: cur ? C.gold : C.bg, display: "flex", alignItems: "center", justifyContent: "center", color: cur ? C.bg : C.dark, fontSize: 11, fontWeight: 700, flexShrink: 0 }}>{i + 1}</div>
            <div style={{ flex: 1 }}>
              <div style={{ display: "flex", justifyContent: "space-between" }}>
                <span style={{ color: C.text, fontSize: 12, fontWeight: 600 }}>{m.n}</span>
                <span style={{ color: C.muted, fontSize: 12 }}>{m.a}</span>
              </div>
              <div style={{ display: "flex", justifyContent: "space-between", marginTop: 2 }}>
                <span style={{ color: C.dark, fontSize: 10 }}>{m.g}</span>
                <span style={{ color: i < 7 ? C.ok : C.dark, fontSize: 9 }}>{i < 7 ? "✓ Confirmed" : `${m.d} days`}</span>
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
}

function MReport() {
  return (
    <div style={{ padding: 14 }}>
      <div style={{ color: C.text, fontSize: 17, fontWeight: 700, marginBottom: 2 }}>Report Sighting</div>
      <div style={{ color: C.muted, fontSize: 11, marginBottom: 18 }}>Submit for scholar verification</div>
      {[
        { l: "Month", el: <select style={{ ...inp, borderRadius: 10 }}><option>Ramadan 1447</option></select> },
        { l: "Your Name", el: <input placeholder="Full name" style={{ ...inp, borderRadius: 10 }} /> },
        { l: "Location", el: <div style={{ display: "flex", gap: 8 }}><input placeholder="City, Country" style={{ ...inp, flex: 1, borderRadius: 10 }} /><button style={{ background: C.goldDim, color: C.gold, border: `1px solid ${C.gold}33`, borderRadius: 10, padding: "0 12px", cursor: "pointer" }}>📍</button></div> },
        { l: "Date & Time", el: <input placeholder="Feb 26, 2026 — 6:45 PM" style={{ ...inp, borderRadius: 10 }} /> },
        { l: "Photo", el: <div style={{ background: C.card, border: `2px dashed ${C.bdr}`, borderRadius: 12, padding: "22px 0", textAlign: "center" }}><div style={{ fontSize: 24 }}>📷</div><div style={{ color: C.muted, fontSize: 11, marginTop: 4 }}>Tap to upload</div></div> },
        { l: "Notes", el: <textarea rows={2} placeholder="Visibility, weather..." style={{ ...inp, borderRadius: 10, resize: "none" }} /> },
      ].map((f, i) => (
        <div key={i} style={{ marginBottom: 14 }}><label style={lbl}>{f.l}</label>{f.el}</div>
      ))}
      <button style={{ width: "100%", background: C.gold, color: C.bg, border: "none", borderRadius: 10, padding: "13px 0", fontSize: 14, fontWeight: 700, cursor: "pointer", marginTop: 4 }}>Submit Report</button>
    </div>
  );
}

function MAnn() {
  return (
    <div style={{ padding: 14 }}>
      <div style={{ color: C.text, fontSize: 17, fontWeight: 700, marginBottom: 14 }}>Announcements</div>
      {anns.map((a, i) => (
        <div key={i} style={{ background: C.card, borderRadius: 12, padding: 14, marginBottom: 9, border: `1px solid ${C.bdr}` }}>
          <div style={{ display: "flex", alignItems: "center", gap: 7, marginBottom: 7 }}>
            <Dot c={priC[a.pri]} />
            <span style={{ padding: "1px 7px", borderRadius: 8, fontSize: 9, background: C.goldDim, color: C.gold, fontWeight: 700, textTransform: "uppercase" }}>{a.type.replace("_", " ")}</span>
            <span style={{ marginLeft: "auto", color: C.dark, fontSize: 10 }}>{a.date}</span>
          </div>
          <div style={{ color: C.text, fontSize: 13, fontWeight: 600 }}>{a.t}</div>
          <div style={{ color: C.muted, fontSize: 13, marginTop: 1 }}>{a.ta}</div>
        </div>
      ))}
    </div>
  );
}

function MSet() {
  return (
    <div style={{ padding: 14 }}>
      <div style={{ color: C.text, fontSize: 17, fontWeight: 700, marginBottom: 18 }}>Settings</div>
      {[
        { s: "General", items: [{ l: "Language", v: "EN / العربية" }, { l: "Calendar", v: "Umm al-Qura" }, { l: "Region", v: "New Zealand" }] },
        { s: "Notifications", items: [{ l: "Month Alerts", v: "on", t: true }, { l: "Sightings", v: "on", t: true }, { l: "Events", v: "on", t: true }] },
        { s: "Account", items: [{ l: "Profile", v: "Mohamed" }, { l: "About", v: "v1.0.0" }] },
      ].map((sec, si) => (
        <div key={si} style={{ marginBottom: 18 }}>
          <div style={{ color: C.gold, fontSize: 10, fontWeight: 700, textTransform: "uppercase", letterSpacing: .8, marginBottom: 8 }}>{sec.s}</div>
          <div style={{ background: C.card, borderRadius: 12, overflow: "hidden", border: `1px solid ${C.bdr}` }}>
            {sec.items.map((it, i) => (
              <div key={i} style={{ display: "flex", justifyContent: "space-between", alignItems: "center", padding: "12px 14px", borderBottom: i < sec.items.length - 1 ? `1px solid ${C.bdr}` : "none" }}>
                <span style={{ color: C.text, fontSize: 12 }}>{it.l}</span>
                {it.t ? (
                  <div style={{ width: 36, height: 20, borderRadius: 10, background: it.v === "on" ? C.gold : C.bg, position: "relative" }}>
                    <div style={{ width: 16, height: 16, borderRadius: "50%", background: "#fff", position: "absolute", top: 2, left: it.v === "on" ? 18 : 2 }} />
                  </div>
                ) : <span style={{ color: C.muted, fontSize: 11 }}>{it.v} ›</span>}
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}

/* ================================================================ */
/* =================== MAIN APP =================================== */
/* ================================================================ */

export default function App() {
  const [mode, setMode] = useState("portal");
  const [aTab, setATab] = useState("dashboard");
  const [mTab, setMTab] = useState("home");
  const [pTab, setPTab] = useState("home");

  const aTabs = [
    { id: "dashboard", l: "🏠 Dashboard" },
    { id: "sightings", l: "🔭 Sightings" },
    { id: "announcements", l: "📢 Announcements" },
    { id: "calendar", l: "📅 Calendar" },
  ];

  const mTabs = [
    { id: "home", l: "Home", ico: "🏠" },
    { id: "calendar", l: "Calendar", ico: "📅" },
    { id: "report", l: "Report", ico: "🔭" },
    { id: "announcements", l: "News", ico: "📢" },
    { id: "settings", l: "Settings", ico: "⚙️" },
  ];

  return (
    <div style={{ fontFamily: "system-ui, -apple-system, sans-serif", minHeight: "100vh", background: mode === "portal" ? C.pBg : C.bg }}>

      {/* ===== MODE SWITCHER ===== */}
      <div style={{ background: "#111827", padding: "10px 24px", display: "flex", alignItems: "center", justifyContent: "space-between", flexWrap: "wrap", gap: 8 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
          <span style={{ fontSize: 18 }}>🌙</span>
          <span style={{ color: C.gold, fontSize: 16, fontWeight: 700 }}>Hilal</span>
          <span style={{ color: "#555", fontSize: 10, textTransform: "uppercase", letterSpacing: 1 }}>Wireframes v1.0</span>
        </div>
        <div style={{ display: "flex", gap: 3, background: "#1a1a2e", borderRadius: 8, padding: 3 }}>
          {[
            { id: "portal", l: "🌐 Public Portal" },
            { id: "admin", l: "🖥️ Admin Panel" },
            { id: "mobile", l: "📱 Mobile App" },
          ].map(v => (
            <button key={v.id} onClick={() => setMode(v.id)} style={{
              background: mode === v.id ? C.gold : "transparent", color: mode === v.id ? "#111" : "#777",
              border: "none", borderRadius: 6, padding: "7px 16px", fontSize: 12, fontWeight: 700, cursor: "pointer"
            }}>{v.l}</button>
          ))}
        </div>
      </div>

      {/* ===== PUBLIC PORTAL ===== */}
      {mode === "portal" && (
        <div style={{ background: C.pBg }}>
          <PNav tab={pTab} setTab={setPTab} />
          {pTab === "home" && <PHome />}
          {pTab === "calendar" && <PCalendar />}
          {pTab === "announcements" && <PAnn />}
          {pTab === "report" && <PReport />}
          {pTab === "about" && <PAbout />}
        </div>
      )}

      {/* ===== ADMIN PORTAL ===== */}
      {mode === "admin" && (
        <div style={{ background: C.bg, color: C.text }}>
          <div style={{ background: C.bg2, borderBottom: `1px solid ${C.bdr}`, padding: "0 24px", display: "flex", gap: 0, overflowX: "auto" }}>
            {aTabs.map(tab => (
              <button key={tab.id} onClick={() => setATab(tab.id)} style={{
                background: "transparent", border: "none", borderBottom: aTab === tab.id ? `2px solid ${C.gold}` : "2px solid transparent",
                color: aTab === tab.id ? C.gold : C.muted, padding: "12px 18px", fontSize: 12, fontWeight: aTab === tab.id ? 700 : 400, cursor: "pointer", whiteSpace: "nowrap"
              }}>{tab.l}</button>
            ))}
          </div>
          <div style={{ padding: "24px 28px", maxWidth: 1100, margin: "0 auto" }}>
            {aTab === "dashboard" && <ADash />}
            {aTab === "sightings" && <ASight />}
            {aTab === "announcements" && <AAnn />}
            {aTab === "calendar" && <ACal />}
          </div>
        </div>
      )}

      {/* ===== MOBILE APP ===== */}
      {mode === "mobile" && (
        <div style={{ background: C.bg, display: "flex", justifyContent: "center", alignItems: "flex-start", padding: "36px 20px", gap: 40, flexWrap: "wrap" }}>
          <div>
            <div style={{ textAlign: "center", marginBottom: 14 }}>
              <div style={{ color: C.gold, fontSize: 13, fontWeight: 700 }}>{mTabs.find(t => t.id === mTab)?.l} Screen</div>
              <div style={{ color: C.dark, fontSize: 10, marginTop: 3 }}>iOS / Android</div>
            </div>
            <div style={{ width: 310, background: C.bg, borderRadius: 34, border: `3px solid ${C.bdr}`, overflow: "hidden", boxShadow: "0 20px 60px rgba(0,0,0,.5)" }}>
              <div style={{ height: 40, background: C.bg2, display: "flex", alignItems: "center", justifyContent: "center", borderBottom: `1px solid ${C.bdr}`, position: "relative" }}>
                <div style={{ width: 90, height: 20, background: C.bg, borderBottomLeftRadius: 14, borderBottomRightRadius: 14, position: "absolute", top: 0 }} />
                <span style={{ fontSize: 10, color: C.muted, position: "absolute", left: 14 }}>9:41</span>
                <span style={{ fontSize: 10, color: C.muted, position: "absolute", right: 14 }}>📶 🔋</span>
              </div>
              <div style={{ height: 540, overflowY: "auto" }}>
                {mTab === "home" && <MHome />}
                {mTab === "calendar" && <MCal />}
                {mTab === "report" && <MReport />}
                {mTab === "announcements" && <MAnn />}
                {mTab === "settings" && <MSet />}
              </div>
              <div style={{ background: C.bg2, borderTop: `1px solid ${C.bdr}`, display: "flex", justifyContent: "space-around", padding: "7px 0 16px" }}>
                {mTabs.map(tab => (
                  <button key={tab.id} onClick={() => setMTab(tab.id)} style={{
                    background: "none", border: "none", cursor: "pointer", display: "flex", flexDirection: "column", alignItems: "center", gap: 2, padding: "3px 6px",
                    color: mTab === tab.id ? C.gold : C.dark
                  }}>
                    <span style={{ fontSize: 16 }}>{tab.ico}</span>
                    <span style={{ fontSize: 8, fontWeight: mTab === tab.id ? 700 : 400 }}>{tab.l}</span>
                  </button>
                ))}
              </div>
            </div>
          </div>
          <div style={{ maxWidth: 300, paddingTop: 46, color: C.text }}>
            <div style={{ color: C.gold, fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Mobile Screens</div>
            {mTabs.map(tab => (
              <div key={tab.id} onClick={() => setMTab(tab.id)} style={{
                padding: "12px 14px", marginBottom: 7, borderRadius: 10, cursor: "pointer",
                background: mTab === tab.id ? C.goldDim : C.card,
                border: `1px solid ${mTab === tab.id ? C.gold + "44" : C.bdr}`
              }}>
                <div style={{ color: mTab === tab.id ? C.gold : C.text, fontSize: 12, fontWeight: 700 }}>{tab.ico} {tab.l}</div>
                <div style={{ color: C.muted, fontSize: 10, marginTop: 3, lineHeight: 1.5 }}>
                  {tab.id === "home" && "Hijri date, countdown, announcement, quick actions."}
                  {tab.id === "calendar" && "Full 12-month Hijri calendar with Gregorian dates."}
                  {tab.id === "report" && "Submit sighting with GPS, photo, notes."}
                  {tab.id === "announcements" && "Bilingual feed with priority & type badges."}
                  {tab.id === "settings" && "Language, region, notifications, profile."}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
