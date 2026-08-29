import React, { useEffect, useMemo, useState } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Link, NavLink, Route, Routes, useLocation } from "react-router-dom";
import { AuthProvider, useAuth } from "./AuthContext";
import { LoginPage, RegisterPage, RegistrationPopup } from "./AuthPages";
import { EventsPage, EventDetailPage } from "./EventPages";
import { apiFetch } from "./apiClient";
import { BRAND_LOGO, BRAND_NAME, FOUNDER_PHOTO } from "./brand";
import "./bootstrap";

const navItems = [
    { label: "About", to: "/" },
    // Hidden for now: { label: "Our Services", to: "/services" },
    { label: "SAKOUR Mission", to: "/mission" },
    { label: "The Three Journeys", to: "/programs" },
    { label: "Events", to: "/events" },
    { label: "Booking", to: "/booking" },
    { label: "Contact", to: "/contact" },
];

const services = [
    {
        title: "Leadership Coaching",
        body: "Unlock your leadership potential with personalized coaching sessions designed for business owners, family business leaders, and teams navigating growth.",
        bullets: ["Lead with clarity and purpose", "Catalyze growth and unlock potential", "Align your vision with meaningful goals"],
        image: "/assets/leadership-image.png",
    },
    {
        title: "Family Business Transformation Authority",
        body: "Navigate generational change with proven methods for succession, governance, and sustainable wealth creation.",
        bullets: ["Facilitate seamless transitions", "Strengthen governance", "Build wealth while preserving legacy"],
        image: "/assets/business-blueprint.png",
    },
    {
        title: "Full Business Blueprint Program",
        body: "Build and scale your business with practical frameworks across strategy, leadership, operations, and profitability.",
        bullets: ["Refine your vision", "Craft winning strategies", "Build a scalable model"],
        image: "/assets/it-consultation.png",
    },
];

const fallbackPrograms = [
    {
        slug: "twelve-weeks",
        title: "12 Weeks Commitment",
        description:
            "<p>Biweekly group coaching addressing the following areas:</p><ul><li>Idea</li><li>Brand</li><li>Productization/ Packages</li><li>Team &amp; Partners</li><li>Cash Flow</li><li>Systems</li></ul>",
        image_url: "/assets/program-12-weeks.png",
        price_cents: 0,
        billing_interval_months: null,
        price_label: "",
    },
    {
        slug: "six-months",
        title: "6 Months Commitment",
        description: "Biweekly group coaching with retreats, leadership game sessions, and wealth-building strategies.",
        image_url: "/assets/program-6-months.png",
        price_cents: 120000,
        billing_interval_months: 1,
        price_label: "£1,200.00 / month",
    },
    {
        slug: "one-year",
        title: "1 Year Commitment",
        description: "Platinum mastermind with retreats, coaching sessions, and global community benefits.",
        image_url: "/assets/program-1-year.png",
        price_cents: 240000,
        billing_interval_months: 12,
        price_label: "£2,400.00 / 12 months",
    },
];

function contactFormDefaults(user) {
    if (!user) {
        return {
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            subject: "",
            question: "",
        };
    }

    const nameParts = String(user.name || "").trim().split(/\s+/);

    return {
        firstName: nameParts[0] || "",
        lastName: nameParts.slice(1).join(" ") || "",
        email: user.email || "",
        phone: user.phone || "",
        subject: "",
        question: "",
    };
}

function ContactInquiryForm() {
    const { user } = useAuth();
    const [formData, setFormData] = useState(() => contactFormDefaults(user));
    const [submitted, setSubmitted] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState("");

    useEffect(() => {
        if (!user) {
            return;
        }

        const defaults = contactFormDefaults(user);
        setFormData((prev) => ({
            ...prev,
            firstName: defaults.firstName,
            lastName: defaults.lastName,
            email: defaults.email,
            phone: defaults.phone,
        }));
    }, [user]);

    function updateField(field, value) {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setSubmitted(false);
        setError("");
    }

    async function handleSubmit(event) {
        event.preventDefault();
        setSubmitting(true);
        setError("");
        setSubmitted(false);

        try {
            const { response, data } = await apiFetch("/api/contact-inquiries", {
                method: "POST",
                body: JSON.stringify({
                    first_name: formData.firstName,
                    last_name: formData.lastName,
                    email: formData.email,
                    phone: formData.phone,
                    subject: formData.subject,
                    question: formData.question,
                }),
            });

            if (!response.ok) {
                throw new Error(data?.message || "Could not send your message. Please try again.");
            }

            setSubmitted(true);
            setFormData(contactFormDefaults(user));
        } catch (submitError) {
            setError(submitError.message || "Could not send your message. Please try again.");
        } finally {
            setSubmitting(false);
        }
    }

    const isLoggedIn = Boolean(user);

    return (
        <form className="booking-form" onSubmit={handleSubmit}>
            <div className="booking-row">
                <input
                    type="text"
                    placeholder="First name"
                    value={formData.firstName}
                    onChange={(e) => updateField("firstName", e.target.value)}
                    readOnly={isLoggedIn}
                    required
                />
                <input
                    type="text"
                    placeholder="Last name"
                    value={formData.lastName}
                    onChange={(e) => updateField("lastName", e.target.value)}
                    readOnly={isLoggedIn}
                    required
                />
            </div>
            <input
                type="email"
                placeholder="Email"
                value={formData.email}
                onChange={(e) => updateField("email", e.target.value)}
                readOnly={isLoggedIn}
                required
            />
            <input
                type="tel"
                placeholder="Phone"
                value={formData.phone}
                onChange={(e) => updateField("phone", e.target.value)}
                readOnly={isLoggedIn}
                required
            />
            <input
                type="text"
                placeholder="Subject"
                value={formData.subject}
                onChange={(e) => updateField("subject", e.target.value)}
                required
            />
            <textarea
                placeholder="Question or comment"
                rows={6}
                value={formData.question}
                onChange={(e) => updateField("question", e.target.value)}
                required
            />
            {error && <p className="booking-error">{error}</p>}
            {submitted && <p className="booking-success">Thank you. Your message has been received.</p>}
            <button type="submit" className="btn btn-primary cursor-pointer" disabled={submitting}>
                {submitting ? "Sending…" : "Submit"}
            </button>
        </form>
    );
}

function EmailVerificationNotice() {
    const { user, resendVerificationEmail } = useAuth();
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");
    const [sending, setSending] = useState(false);

    if (!user || user.email_verified) {
        return null;
    }

    async function handleResend() {
        setSending(true);
        setError("");
        setMessage("");
        try {
            const text = await resendVerificationEmail();
            setMessage(text);
        } catch (err) {
            setError(err.message);
        } finally {
            setSending(false);
        }
    }

    return (
        <div className="email-verify-banner container">
            <p>
                Please verify your email address. Check your inbox for the welcome email, or{" "}
                <button type="button" className="email-verify-banner-link" disabled={sending} onClick={handleResend}>
                    {sending ? "Sending…" : "resend verification email"}
                </button>
                .
            </p>
            {message && <p className="booking-success">{message}</p>}
            {error && <p className="booking-error">{error}</p>}
        </div>
    );
}

function CookieNotice({ noticeText }) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        try {
            setVisible(localStorage.getItem("cookie_notice_ack") !== "1");
        } catch {
            setVisible(true);
        }
    }, []);

    function acknowledge() {
        try {
            localStorage.setItem("cookie_notice_ack", "1");
        } catch {
            // ignore storage errors
        }
        setVisible(false);
    }

    if (!visible || !noticeText) {
        return null;
    }

    return (
        <div className="cookie-notice" role="region" aria-label="Cookie notice">
            <div className="container cookie-notice-inner">
                <p className="cookie-notice-text">
                    {noticeText}{" "}
                    <Link className="cookie-notice-link" to="/cookies">Cookie policy</Link>
                    {" · "}
                    <Link className="cookie-notice-link" to="/privacy">Privacy policy</Link>
                </p>
                <button type="button" className="btn btn-dark cookie-notice-btn" onClick={acknowledge}>
                    OK
                </button>
            </div>
        </div>
    );
}

function Layout({ children }) {
    const location = useLocation();
    const { user, logout } = useAuth();
    const [legalContent, setLegalContent] = useState(null);

    useEffect(() => {
        async function loadLegalContent() {
            try {
                const response = await fetch("/api/legal-content");
                const data = await response.json();
                setLegalContent(data.content ?? null);
            } catch {
                setLegalContent(null);
            }
        }

        loadLegalContent();
    }, []);

    return (
        <div className="page">
            <EmailVerificationNotice />
            <header className="topbar">
                <div className="container topbar-inner">
                    <a className="brand" href="/" aria-label={BRAND_NAME}>
                        <img src={BRAND_LOGO} alt={`${BRAND_NAME} logo`} />
                    </a>
                    <nav className="nav">
                        {navItems.map((item) => {
                            const key = typeof item.to === "string" ? item.to : `${item.label}-${item.to.pathname}${item.to.hash ?? ""}`;
                            return (
                                <NavLink
                                    key={key}
                                    to={item.to}
                                    className={({ isActive }) => {
                                        if (item.label === "About") {
                                            const active = location.pathname === "/" && !location.hash;
                                            return `nav-link ${active ? "active" : ""}`;
                                        }
                                        return `nav-link ${isActive ? "active" : ""}`;
                                    }}
                                >
                                    {item.label}
                                </NavLink>
                            );
                        })}
                        {user ? (
                            <>
                                <span className="nav-link nav-link-muted">Hi, {user.name.split(" ")[0]}</span>
                                <button type="button" className="nav-link nav-link-btn" onClick={() => logout()}>
                                    Log out
                                </button>
                            </>
                        ) : (
                            <>
                                <NavLink
                                    to="/login"
                                    className={({ isActive }) => `nav-link ${isActive ? "active" : ""}`}
                                >
                                    Log in
                                </NavLink>
                                <NavLink
                                    to="/register"
                                    className={({ isActive }) => `nav-link nav-link-register ${isActive ? "active" : ""}`}
                                >
                                    Register
                                </NavLink>
                            </>
                        )}
                    </nav>
                </div>
            </header>
            <main>{children}</main>
            <CookieNotice noticeText={legalContent?.cookie_notice_text} />
            <footer className="footer">
                <div className="container footer-inner">
                    <p>{BRAND_NAME}</p>
                    <p className="footer-links">
                        <Link to="/privacy">Privacy policy</Link>
                        <span aria-hidden="true"> · </span>
                        <Link to="/cookies">Cookies</Link>
                    </p>
                    <p>Copyright {BRAND_NAME} 2024-2026.</p>
                </div>
            </footer>
        </div>
    );
}

function MultilineText({ text }) {
    if (!text) {
        return null;
    }

    const lines = String(text).split("\n").filter((line) => line.trim() !== "");

    return lines.map((line, index) => (
        <React.Fragment key={index}>
            {line}
            {index < lines.length - 1 ? <br /> : null}
        </React.Fragment>
    ));
}

function PageLink({ to, className, children }) {
    const href = to || "/";

    if (href.startsWith("http://") || href.startsWith("https://")) {
        return (
            <a className={className} href={href} target="_blank" rel="noopener noreferrer">
                {children}
            </a>
        );
    }

    return (
        <Link className={className} to={href}>
            {children}
        </Link>
    );
}

function HomePage() {
    const location = useLocation();
    const { refreshUser } = useAuth();
    const query = useMemo(() => new URLSearchParams(location.search), [location.search]);
    const paymentResult = query.get("payment");
    const [showHomePopup, setShowHomePopup] = useState(false);
    const [homePopupMessage, setHomePopupMessage] = useState("");
    const [content, setContent] = useState(null);

    const verifiedResult = query.get("verified");

    useEffect(() => {
        async function loadContent() {
            try {
                const response = await fetch("/api/page-content");
                const data = await response.json();
                setContent(data.content ?? null);
            } catch {
                setContent(null);
            }
        }

        loadContent();
    }, []);

    useEffect(() => {
        if (paymentResult === "cancelled") {
            setHomePopupMessage("Payment was cancelled. Please book again when ready.");
            setShowHomePopup(true);
        } else if (verifiedResult === "1") {
            refreshUser();
            setHomePopupMessage("Your email has been verified. Thank you!");
            setShowHomePopup(true);
        } else if (verifiedResult === "already") {
            setHomePopupMessage("Your email was already verified.");
            setShowHomePopup(true);
        } else if (verifiedResult === "invalid") {
            setHomePopupMessage("This verification link is invalid or has expired.");
            setShowHomePopup(true);
        }
    }, [paymentResult, verifiedResult]);

    function closeHomePopup() {
        setShowHomePopup(false);
        const url = new URL(window.location.href);
        url.searchParams.delete("payment");
        url.searchParams.delete("session_id");
        url.searchParams.delete("verified");
        window.history.replaceState({}, "", `${url.pathname}${url.search}${url.hash}`);
    }

    if (!content) {
        return (
            <Layout>
                <section className="container section">
                    <p className="lead">Loading…</p>
                </section>
            </Layout>
        );
    }

    return (
        <Layout>
            <section
                className="hero-wrap"
                style={{
                    backgroundImage: `linear-gradient(rgba(12, 11, 10, 0.62), rgba(12, 11, 10, 0.72)), url("${content.hero_banner_url}")`,
                    backgroundSize: "cover",
                    backgroundPosition: "center",
                    backgroundRepeat: "no-repeat",
                }}
            >
                <div className="hero container">
                    <p className="eyebrow">{content.hero_eyebrow}</p>
                    <h1>
                        {content.hero_title_line_1}
                        <br />
                        {content.hero_title_line_2}
                    </h1>
                    <p className="lead">{content.hero_lead}</p>
                    <div className="hero-actions">
                        <PageLink className="btn btn-primary" to={content.hero_cta_link}>
                            {content.hero_cta_text}
                        </PageLink>
                    </div>
                </div>
            </section>
            <section className="container section intro-section">
                <p className="eyebrow">{content.about_eyebrow}</p>
                <h2>{content.about_heading}</h2>
                <p className="lead">
                    <MultilineText text={content.about_lead} />
                </p>
            </section>
            <section className="container section who-we-serve-section">
                <p className="eyebrow">{content.who_eyebrow}</p>
                <h2>{content.who_heading}</h2>
                <p className="lead">{content.who_lead}</p>

                <div className="audiences-block">
                    <p className="eyebrow">Who We Work With</p>
                    <div className="audiences-grid">
                        {(content.audiences || []).map((audience, index) => (
                            <article className="card audience-card" key={audience.title || index}>
                                <span className="section-number">{String(index + 1).padStart(2, "0")}</span>
                                <h3>{audience.title}</h3>
                                <p>{audience.body}</p>
                            </article>
                        ))}
                    </div>
                </div>

                <div className="belief-block">
                    <p className="eyebrow">{content.belief_eyebrow}</p>
                    <p className="lead">{content.belief_lead}</p>
                </div>

                <div className="why-now-block">
                    <p className="eyebrow">{content.why_now_eyebrow}</p>
                    <h3>{content.why_now_heading}</h3>
                    <div className="insights-grid">
                        {(content.why_now_insights || []).map((insight, index) => (
                            <article className="insight" key={index}>
                                <p>{insight.text}</p>
                            </article>
                        ))}
                    </div>
                    <p className="research-source">{content.why_now_source}</p>
                </div>

                <div className="transform-block">
                    <p className="eyebrow">{content.transform_eyebrow}</p>
                    <div className="transform-grid">
                        {(content.transforms || []).map((item, index) => (
                            <article className="card transform-card" key={`${item.from}-${index}`}>
                                <span className="transform-from">{item.from}</span>
                                <span className="transform-arrow" aria-hidden="true">&rarr;</span>
                                <span className="transform-to">{item.to}</span>
                            </article>
                        ))}
                    </div>
                </div>

                <div className="process-block">
                    <p className="eyebrow">{content.process_eyebrow}</p>
                    <h3>{content.process_heading}</h3>
                    <div className="process-grid">
                        {(content.process_steps || []).map((step, index) => (
                            <article className="process-step" key={`${step.label}-${index}`}>
                                <span className="section-number">{String(index + 1).padStart(2, "0")}</span>
                                <div>
                                    <p className="process-label">{step.label}</p>
                                    <h4>{step.title}</h4>
                                    <p>{step.body}</p>
                                </div>
                            </article>
                        ))}
                    </div>
                </div>

                <div className="why-block">
                    <p className="eyebrow">{content.why_eyebrow}</p>
                    <h3 className="why-tagline">{content.why_tagline}</h3>
                    <p>{content.why_paragraph_1}</p>
                    <p>{content.why_paragraph_2}</p>
                    <p>{content.why_paragraph_3}</p>
                </div>

                <div className="regions-block">
                    <p className="eyebrow">{content.regions_eyebrow}</p>
                    <p className="lead">{content.regions_lead}</p>
                    <div className="regions-grid">
                        {(content.regions || []).map((region, index) => (
                            <article className="card region-card" key={region.title || index}>
                                <h3>{region.title}</h3>
                                <p>{region.body}</p>
                            </article>
                        ))}
                    </div>
                </div>
            </section>
            <section className="container section founder-section">
                <div
                    className="founder-photo"
                    role="img"
                    aria-label={content.founder_name}
                    style={{ backgroundImage: `linear-gradient(rgba(17, 16, 13, 0.2), rgba(17, 16, 13, 0.12)), url("${FOUNDER_PHOTO}")` }}
                />
                <article className="card founder-card">
                    <p className="eyebrow">{content.founder_eyebrow}</p>
                    <h3>{content.founder_name}</h3>
                    <p className="founder-role">{content.founder_role}</p>
                    <p className="founder-hook">{content.founder_hook}</p>
                    <p>{content.founder_paragraph_1}</p>
                    <p>{content.founder_paragraph_2}</p>
                    <p>{content.founder_paragraph_3}</p>
                    <ul className="founder-credentials">
                        {(content.founder_credentials || []).map((credential, index) => (
                            <li key={index}>{credential}</li>
                        ))}
                    </ul>
                    <blockquote className="founder-quote">
                        <p>{content.founder_quote}</p>
                        <footer>{content.founder_quote_footer}</footer>
                    </blockquote>
                </article>
            </section>
            <section className="container section">
                <article className="card cta-card">
                    <p className="eyebrow">{content.cta_eyebrow}</p>
                    <h3>{content.cta_heading}</h3>
                    <p>{content.cta_body}</p>
                    <PageLink className="btn btn-primary" to={content.cta_button_link}>
                        {content.cta_button_text}
                    </PageLink>
                </article>
            </section>
            {showHomePopup && (
                <div className="booking-success-overlay" role="dialog" aria-modal="true" aria-label="Payment update">
                    <div className="booking-success-modal">
                        <h3>{verifiedResult ? "Email verified" : "Payment update"}</h3>
                        <p>{homePopupMessage}</p>
                        <button type="button" className="btn btn-primary" onClick={closeHomePopup}>
                            Close
                        </button>
                    </div>
                </div>
            )}
        </Layout>
    );
}

function MissionPage() {
    const [content, setContent] = useState(null);

    useEffect(() => {
        async function loadContent() {
            try {
                const response = await fetch("/api/page-content");
                const data = await response.json();
                setContent(data.content ?? null);
            } catch {
                setContent(null);
            }
        }

        loadContent();
    }, []);

    if (!content) {
        return (
            <Layout>
                <section className="container section">
                    <p className="lead">Loading…</p>
                </section>
            </Layout>
        );
    }

    const bannerStyle = {
        backgroundImage: `linear-gradient(rgba(22, 18, 10, 0.58), rgba(22, 18, 10, 0.62)), url("${content.mission_banner_url}")`,
        backgroundSize: "cover",
        backgroundPosition: "center",
        backgroundRepeat: "no-repeat",
    };

    return (
        <Layout>
            <section className="impact-band mission-hero" style={bannerStyle}>
                <div className="container impact-inner">
                    <p className="eyebrow">{content.mission_eyebrow}</p>
                    <h2>{content.mission_heading}</h2>
                    <p>{content.mission_paragraph_1}</p>
                    <p>{content.mission_paragraph_2}</p>
                    <p className="impact-closing">{content.mission_closing}</p>
                </div>
            </section>
            <section className="container section">
                <article className="card cta-card">
                    <p className="eyebrow">{content.mission_cta_eyebrow}</p>
                    <h3>{content.mission_cta_heading}</h3>
                    <p>{content.mission_cta_body}</p>
                    <PageLink className="btn btn-primary" to={content.mission_cta_button_link}>
                        {content.mission_cta_button_text}
                    </PageLink>
                </article>
            </section>
        </Layout>
    );
}

function ServicesPage() {
    return (
        <Layout>
            <section className="container section services-page">
                <p className="eyebrow">Our Services</p>
                <h2>Transformative experiences that inspire conscious leadership.</h2>
                <p className="lead">
                    At {BRAND_NAME}, we are dedicated to creating transformative experiences that inspire conscious leadership and
                    meaningful impact. Our services are designed to foster deep, authentic connections through personalized coaching,
                    thought-provoking workshops, and innovative events.
                </p>
                <p className="lead">
                    Raouda's unique approach blends wisdom, empathy, and strategic insight to help individuals and organizations
                    unlock their potential and make a lasting difference. Whether it's through intimate one-on-one sessions or dynamic
                    group settings, {BRAND_NAME} is a haven for growth, where each conversation ignites new possibilities for personal and
                    professional development.
                </p>

                <div className="services-offers">
                    <article className="card offer-card">
                        <div className="offer-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L14.4 8.4L21 10.8L14.4 13.2L12 20L9.6 13.2L3 10.8L9.6 8.4L12 2Z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round"/>
                                <path d="M14.8 9.2L19 5" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round"/>
                            </svg>
                        </div>
                        <p className="offer-duration">30 - 45 minutes</p>
                        <h3>Strategy Session</h3>
                        <p>Helps me understand if I can help or not. If I can help I need to know where.</p>
                    </article>

                    <article className="card offer-card">
                        <div className="offer-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 21s-7-4.6-9.4-9C.8 7 3.5 4 6.9 4c1.7 0 3.2.8 4.1 2c.9-1.2 2.4-2 4.1-2c3.4 0 6.1 3 4.3 8c-2.4 4.4-9.4 9-9.4 9Z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round"/>
                                <path d="M7.6 13.1l1.9 1.9 5.2-5.2" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"/>
                            </svg>
                        </div>
                        <p className="offer-duration">6 months</p>
                        <h3>Raouda's coaching</h3>
                        <ul>
                            <li>Includes: two retreats</li>
                            <li>The Leadership Game</li>
                            <li>Wealth building strategies and more</li>
                        </ul>
                    </article>

                    <article className="card offer-card">
                        <div className="offer-icon" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2l3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7Z" stroke="currentColor" strokeWidth="1.6" strokeLinejoin="round"/>
                                <path d="M8.5 12h7" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round"/>
                            </svg>
                        </div>
                        <p className="offer-duration">12-month programme</p>
                        <h3>12-month programme</h3>
                        <ul>
                            <li>Includes: four retreats</li>
                            <li>The Leadership Game</li>
                            <li>Wealth building strategies and more</li>
                            <li>Circle of Excellence Membership</li>
                        </ul>
                    </article>
                </div>

                <div className="services-programs-cta">
                    <Link className="btn btn-primary" to="/programs">
                        The Three Journeys
                    </Link>
                </div>

                <div className="stack services-list" aria-label="Service Areas">
                    {services.map((item) => (
                        <article className="card" key={item.title}>
                            <img src={item.image} alt={item.title} className="service-card-image" />
                            <h3>{item.title}</h3>
                            <p>{item.body}</p>
                            <ul>
                                {item.bullets.map((bullet) => (
                                    <li key={bullet}>{bullet}</li>
                                ))}
                            </ul>
                        </article>
                    ))}
                </div>
            </section>
        </Layout>
    );
}

function ProgramsPage() {
    const [programs, setPrograms] = useState(fallbackPrograms);

    useEffect(() => {
        let cancelled = false;

        async function fetchPrograms() {
            try {
                const response = await fetch("/api/programs");
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                const items = Array.isArray(payload.programs) ? payload.programs : [];
                if (!cancelled && items.length > 0) {
                    setPrograms(items);
                }
            } catch {
                // Keep fallback programs when API is unavailable.
            }
        }

        fetchPrograms();

        return () => {
            cancelled = true;
        };
    }, []);

    return (
        <Layout>
            <section className="programs-hero">
                <div className="container">
                    <h1>The Three Journeys</h1>
                </div>
            </section>
            <section className="container section programs-intro">
                <p className="lead">
                    Every engagement begins with The Legacy Conversation — a complimentary, private conversation. From there, three
                    journeys — each one deeper than the last.
                </p>
                <div className="programs-stack">
                    {programs.map((program) => (
                        <article className="card program-card" key={program.slug}>
                            <div
                                className="program-image"
                                style={{
                                    backgroundImage: `linear-gradient(to bottom, rgba(12, 11, 10, 0.15), rgba(12, 11, 10, 0.4)), url("${program.image_url || "/assets/program-12-weeks.png"}")`,
                                }}
                            />
                            <div className="program-content">
                                {program.price_label && program.price_label.toLowerCase() !== "free" ? (
                                    <p className="duration">{program.price_label}</p>
                                ) : null}
                                <h3>{program.title}</h3>
                                <div
                                    className="program-details"
                                    dangerouslySetInnerHTML={{
                                        __html: program.description || "<p>Program details will be shared during booking.</p>",
                                    }}
                                />
                                <a className="btn btn-dark" href={`/booking?plan=${program.slug}`}>
                                    Book now
                                </a>
                            </div>
                        </article>
                    ))}
                </div>
            </section>
            <section className="programs-contact">
                <div className="container programs-contact-inner">
                    <p className="eyebrow">Get In Touch</p>
                    <h2>We would love to hear from you.</h2>
                    <p className="lead">
                        Send us a message with your question or comment. We will get back to you as soon as we can.
                    </p>
                    <article className="card booking-card contact-page-card lead">
                        <ContactInquiryForm />
                    </article>
                </div>
            </section>
        </Layout>
    );
}

/** Parse "9:00 AM" / "2:30 PM" on a calendar day in the user's local timezone (matches API slot strings). */
function parseBookingSlotLocal(date, slotLabel) {
    const match = /^(\d{1,2}):(\d{2})\s*(AM|PM)$/i.exec(String(slotLabel).trim());
    if (!match) {
        return null;
    }
    let hour = parseInt(match[1], 10);
    const minute = parseInt(match[2], 10);
    const mer = match[3].toUpperCase();
    if (mer === "PM" && hour !== 12) {
        hour += 12;
    }
    if (mer === "AM" && hour === 12) {
        hour = 0;
    }
    return new Date(date.getFullYear(), date.getMonth(), date.getDate(), hour, minute, 0, 0);
}

function isBookingSlotInPast(date, slotLabel) {
    const start = parseBookingSlotLocal(date, slotLabel);
    if (!start) {
        return false;
    }
    return start.getTime() < Date.now();
}

function BookingPage() {
    const location = useLocation();
    const query = useMemo(() => new URLSearchParams(location.search), [location.search]);
    const selectedProgramPlan = query.get("plan");
    const paymentResult = query.get("payment");
    const [programCatalog, setProgramCatalog] = useState(fallbackPrograms);
    const selectedProgram = useMemo(
        () => programCatalog.find((program) => program.slug === selectedProgramPlan) || null,
        [programCatalog, selectedProgramPlan]
    );
    const isPaidProgram = Boolean(selectedProgram && Number(selectedProgram.price_cents) >= 100);

    const timeSlots = useMemo(() => {
        const out = [];
        for (let mins = 10 * 60; mins <= 17 * 60; mins += 30) {
            const h24 = Math.floor(mins / 60);
            const m = mins % 60;
            const isPm = h24 >= 12;
            let h12 = h24 % 12;
            if (h12 === 0) {
                h12 = 12;
            }
            const mm = m === 0 ? "00" : "30";
            out.push(`${h12}:${mm} ${isPm ? "PM" : "AM"}`);
        }
        return out;
    }, []);

    const availableDates = useMemo(() => {
        const dates = [];
        const cursor = new Date();
        cursor.setHours(0, 0, 0, 0);
        while (dates.length < 14) {
            const dow = cursor.getDay();
            if (dow !== 0 && dow !== 6) {
                dates.push(new Date(cursor));
            }
            cursor.setDate(cursor.getDate() + 1);
        }
        return dates;
    }, []);

    const [selectedDate, setSelectedDate] = useState(availableDates[0]);
    const [selectedTime, setSelectedTime] = useState("");
    const [bookedSlots, setBookedSlots] = useState([]);
    const [unavailableSlots, setUnavailableSlots] = useState([]);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [submitError, setSubmitError] = useState("");
    const [submitLocked, setSubmitLocked] = useState(false);
    const [showSuccessPopup, setShowSuccessPopup] = useState(false);
    const [successPopupMessage, setSuccessPopupMessage] = useState("");
    const [hasShownPaymentPopup, setHasShownPaymentPopup] = useState(false);
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        phone: "",
        message: "",
    });
    const [submitted, setSubmitted] = useState(false);
    const [clockTick, setClockTick] = useState(0);

    useEffect(() => {
        let cancelled = false;

        async function fetchPrograms() {
            try {
                const response = await fetch("/api/programs");
                if (!response.ok) {
                    return;
                }
                const payload = await response.json();
                const items = Array.isArray(payload.programs) ? payload.programs : [];
                if (!cancelled && items.length > 0) {
                    setProgramCatalog(items);
                }
            } catch {
                // Keep fallback programs when API is unavailable.
            }
        }

        fetchPrograms();

        return () => {
            cancelled = true;
        };
    }, []);

    useEffect(() => {
        const id = window.setInterval(() => setClockTick((n) => n + 1), 60000);
        return () => window.clearInterval(id);
    }, []);

    function updateField(field, value) {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setSubmitted(false);
        setSubmitError("");
    }

    function selectedDateIso() {
        return selectedDate.toLocaleDateString("en-CA");
    }

    function resetBookingFields() {
        setFormData({
            firstName: "",
            lastName: "",
            email: "",
            phone: "",
            message: "",
        });
        setSelectedTime("");
        setSubmitted(false);
    }

    function openSuccessPopup(message) {
        setSuccessPopupMessage(message);
        setShowSuccessPopup(true);
    }

    useEffect(() => {
        let cancelled = false;
        async function fetchAvailability() {
            setLoadingSlots(true);
            try {
                const response = await fetch(`/api/bookings/availability?date=${selectedDateIso()}`);
                const data = await response.json();
                if (!cancelled) {
                    const booked = Array.isArray(data.booked) ? data.booked : [];
                    const unavailable = Array.isArray(data.unavailable) ? data.unavailable : [];
                    setBookedSlots(booked);
                    setUnavailableSlots(unavailable);
                }
            } catch {
                if (!cancelled) {
                    setBookedSlots([]);
                    setUnavailableSlots([]);
                }
            } finally {
                if (!cancelled) {
                    setLoadingSlots(false);
                }
            }
        }

        fetchAvailability();

        return () => {
            cancelled = true;
        };
    }, [selectedDate]);

    useEffect(() => {
        if (!selectedTime) {
            return;
        }
        if (
            bookedSlots.includes(selectedTime) ||
            unavailableSlots.includes(selectedTime) ||
            isBookingSlotInPast(selectedDate, selectedTime)
        ) {
            setSelectedTime("");
        }
    }, [selectedDate, selectedTime, bookedSlots, unavailableSlots, clockTick]);

    useEffect(() => {
        if (paymentResult === "success" && !hasShownPaymentPopup) {
            resetBookingFields();
            openSuccessPopup("Payment received. Your appointment is confirmed. Check your email for details.");
            setHasShownPaymentPopup(true);
        }
    }, [paymentResult, hasShownPaymentPopup]);

    async function handleSubmit(event) {
        event.preventDefault();
        if (submitLocked) {
            return;
        }
        setSubmitLocked(true);
        window.setTimeout(() => {
            setSubmitLocked(false);
        }, 3000);
        setSubmitError("");
        if (!selectedTime) {
            return;
        }
        try {
            const endpoint = isPaidProgram ? "/api/bookings/checkout-session" : "/api/bookings";
            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    first_name: formData.firstName,
                    last_name: formData.lastName,
                    email: formData.email,
                    phone: formData.phone,
                    message: formData.message,
                    date: selectedDateIso(),
                    time: selectedTime,
                    program_plan_key: selectedProgramPlan,
                    return_base_url: window.location.origin,
                }),
            });

            if (response.status === 409) {
                setSubmitError("This time slot was just booked. Please choose another one.");
                setSelectedTime("");
                setSubmitted(false);
                return;
            }

            if (!response.ok) {
                setSubmitError("We could not complete your booking. Please try again.");
                setSubmitted(false);
                return;
            }

            if (isPaidProgram) {
                const payload = await response.json();
                if (payload.checkout_url) {
                    window.location.href = payload.checkout_url;
                    return;
                }
                setSubmitError("Could not start payment checkout. Please try again.");
                return;
            }

            setSubmitted(true);
            resetBookingFields();
            openSuccessPopup("Thanks! Your appointment is confirmed. Check your email for details.");
        } catch {
            setSubmitError("Something went wrong while submitting your booking.");
            setSubmitted(false);
        }
    }

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Booking</p>
                <h2>Choose a date, time slot, and submit your details.</h2>
                {isPaidProgram && (
                    <p className="lead">
                        You selected <strong>{selectedProgram?.title}</strong> ({selectedProgram?.price_label}). After
                        submitting this form, you will be redirected to Stripe to start your subscription.
                    </p>
                )}
                {paymentResult === "cancelled" && (
                    <p className="booking-error">Payment was cancelled. You can submit again to retry checkout.</p>
                )}
                <article className="card booking-card booking-layout">
                    <div className="calendar-panel">
                        <h3>Select a Date</h3>
                        <p className="booking-dates-note">Weekdays only — Saturday and Sunday are not available.</p>
                        <div className="date-grid">
                            {availableDates.map((date) => {
                                const isActive = date.toDateString() === selectedDate.toDateString();
                                const label = date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
                                const weekday = date.toLocaleDateString("en-US", { weekday: "short" });

                                return (
                                    <button
                                        key={date.toISOString()}
                                        type="button"
                                        className={`date-pill ${isActive ? "active" : ""}`}
                                        onClick={() => {
                                            setSelectedDate(date);
                                            setSelectedTime("");
                                            setSubmitted(false);
                                        }}
                                    >
                                        <span>{weekday}</span>
                                        <strong>{label}</strong>
                                    </button>
                                );
                            })}
                        </div>

                        <h3>Available Time Slots</h3>
                        <div className="slot-grid">
                            {timeSlots.map((slot) => {
                                const isBooked = bookedSlots.includes(slot);
                                const isUnavailable = unavailableSlots.includes(slot);
                                const isPast = isBookingSlotInPast(selectedDate, slot);
                                const isDisabled = isBooked || isUnavailable || isPast || loadingSlots;
                                return (
                                    <button
                                        key={slot}
                                        type="button"
                                        className={`slot-pill ${selectedTime === slot ? "active" : ""} ${isBooked || isUnavailable || isPast ? "disabled" : ""}`}
                                        disabled={isDisabled}
                                        onClick={() => {
                                            if (isDisabled) {
                                                return;
                                            }
                                            setSelectedTime(slot);
                                            setSubmitted(false);
                                            setSubmitError("");
                                        }}
                                    >
                                        {slot}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div className="booking-form-panel">
                        <p className="booking-meta">
                            <strong>Selected:</strong>{" "}
                            {selectedDate.toLocaleDateString("en-US", {
                                weekday: "long",
                                month: "short",
                                day: "numeric",
                                year: "numeric",
                            })}{" "}
                            {selectedTime ? `at ${selectedTime}` : "(choose a time slot)"}
                        </p>

                        <form className="booking-form" onSubmit={handleSubmit}>
                            <div className="booking-row">
                                <input
                                    type="text"
                                    placeholder="First name"
                                    value={formData.firstName}
                                    onChange={(e) => updateField("firstName", e.target.value)}
                                    required
                                />
                                <input
                                    type="text"
                                    placeholder="Last name"
                                    value={formData.lastName}
                                    onChange={(e) => updateField("lastName", e.target.value)}
                                    required
                                />
                            </div>
                            <input
                                type="email"
                                placeholder="Email"
                                value={formData.email}
                                onChange={(e) => updateField("email", e.target.value)}
                                required
                            />
                            <input
                                type="tel"
                                placeholder="Phone"
                                value={formData.phone}
                                onChange={(e) => updateField("phone", e.target.value)}
                                required
                            />
                            <textarea
                                placeholder="Message"
                                rows={5}
                                value={formData.message}
                                onChange={(e) => updateField("message", e.target.value)}
                                required
                            />
                            {!selectedTime && <p className="booking-error">Please choose a time slot before submitting.</p>}
                            {submitError && <p className="booking-error">{submitError}</p>}
                            {submitted && <p className="booking-success">Thanks! Your appointment is confirmed. Check your email for details.</p>}
                            <button type="submit" className="btn btn-primary cursor-pointer" disabled={submitLocked}>
                                {isPaidProgram ? "Proceed to Payment" : "Submit"}
                            </button>
                        </form>
                    </div>
                </article>

                {showSuccessPopup && (
                    <div className="booking-success-overlay" role="dialog" aria-modal="true" aria-label="Booking success">
                        <div className="booking-success-modal">
                            <h3>Success</h3>
                            <p>{successPopupMessage}</p>
                            <button
                                type="button"
                                className="btn btn-primary cursor-pointer"
                                onClick={() => {
                                    setShowSuccessPopup(false);
                                    const url = new URL(window.location.href);
                                    url.searchParams.delete("payment");
                                    url.searchParams.delete("session_id");
                                    window.history.replaceState({}, "", `${url.pathname}${url.search}${url.hash}`);
                                }}
                            >
                                Close
                            </button>
                        </div>
                    </div>
                )}
            </section>
        </Layout>
    );
}

function ContactPage() {
    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Contact Us</p>
                <h2>We would love to hear from you.</h2>
                <p className="lead">
                    Send us a message with your question or comment. We will get back to you as soon as we can.
                </p>
                <article className="card booking-card contact-page-card">
                    <ContactInquiryForm />
                </article>
            </section>
        </Layout>
    );
}

function LegalPolicyPage({ eyebrow, title, htmlKey }) {
    const [content, setContent] = useState(null);

    useEffect(() => {
        async function loadContent() {
            try {
                const response = await fetch("/api/legal-content");
                const data = await response.json();
                setContent(data.content ?? null);
            } catch {
                setContent(null);
            }
        }

        loadContent();
    }, []);

    if (!content) {
        return (
            <Layout>
                <section className="container section">
                    <p className="lead">Loading…</p>
                </section>
            </Layout>
        );
    }

    return (
        <Layout>
            <section className="container section legal-page">
                <p className="eyebrow">{eyebrow}</p>
                <h2>{title}</h2>
                <article className="card legal-content-card">
                    <div
                        className="legal-content-body program-details"
                        dangerouslySetInnerHTML={{ __html: content[htmlKey] || "" }}
                    />
                </article>
            </section>
        </Layout>
    );
}

function PrivacyPage() {
    return <LegalPolicyPage eyebrow="Legal" title="Privacy policy" htmlKey="privacy_policy_html" />;
}

function CookiesPage() {
    return <LegalPolicyPage eyebrow="Legal" title="Cookie policy" htmlKey="cookie_policy_html" />;
}

function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <RegistrationPopup />
                <Routes>
                    <Route path="/" element={<HomePage />} />
                    {/* Hidden for now: <Route path="/services" element={<ServicesPage />} /> */}
                    <Route path="/mission" element={<MissionPage />} />
                    <Route path="/programs" element={<ProgramsPage />} />
                    <Route path="/events" element={<EventsPage Layout={Layout} />} />
                    <Route path="/events/:slug" element={<EventDetailPage Layout={Layout} />} />
                    <Route path="/booking" element={<BookingPage />} />
                    <Route path="/contact" element={<ContactPage />} />
                    <Route path="/privacy" element={<PrivacyPage />} />
                    <Route path="/cookies" element={<CookiesPage />} />
                    <Route path="/login" element={<LoginPage Layout={Layout} />} />
                    <Route path="/register" element={<RegisterPage Layout={Layout} />} />
                </Routes>
            </BrowserRouter>
        </AuthProvider>
    );
}

createRoot(document.getElementById("app")).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
