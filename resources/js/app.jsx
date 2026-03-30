import React, { useEffect, useMemo, useState } from "react";
import { createRoot } from "react-dom/client";
import { BrowserRouter, Link, NavLink, Route, Routes, useLocation } from "react-router-dom";
import "./bootstrap";

const navItems = [
    { label: "About", to: "/" },
    { label: "Our Services", to: "/services" },
    { label: "Giving Back", to: { pathname: "/", hash: "#giving-back" } },
    { label: "Our Programs", to: "/programs" },
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

const programs = [
    {
        key: "twelve-weeks",
        duration: "12 Weeks Time Commitment",
        name: "12 Weeks Commitment",
        kicker: "NO FINANCIAL OBLIGATION",
        details: "Biweekly group coaching addressing the following areas:",
        points: [
            "Idea",
            "Brand",
            "Productization/ Packages",
            "Team & Partners",
            "Cash Flow",
            "Systems",
        ],
        ctaLabel: "Book now",
    },
    {
        key: "six-months",
        duration: "6 Months Commitment",
        name: "6 Months Commitment",
        details: "Biweekly group coaching addressing the following areas:",
        points: [
            "Fundraising",
            "Idea",
            "Brand",
            "Productization/ Packages",
            "Team & Partners",
            "Cash Flow",
            "Systems",
            "Kick off Retreat: two days and a night in a 4-star hotel in the UK (all included) - Mind and Life Mastery Event - (Oct 2024)",
            "The Leadership Game in the Age of AI (2 sessions)",
            "Wealth Building Strategies",
            "Closing Retreat: two days and a night in a 4-star hotel in the UK (all included) - Break-Through to Success - (Mar 2025)",
        ],
        ctaLabel: "Book now",
    },
    {
        key: "one-year",
        duration: "1 Year Commitment",
        name: "1 Year Commitment",
        details: "Biweekly Platinum Mastermind Session including:",
        points: [
            "The Circle of Excellence Memberships with global community weekly webinar every Tuesday with global entrepreneurs",
            "Kick off Retreat: two days and a night in a 4-star hotel in the UK (all included) - Mind and Life Mastery Event (Nov 2024)",
            "Second Retreat: two days and a night in a 4-star hotel in the UK (all included) - Break-through to Success (Mar 2025)",
            "Third Retreat: two days and a night - Scale Your Business - Sales and Marketing. In a 4-star hotel in the UK (Jun 2025)",
            "Fourth Retreat: Bali Business School – 5 days event fully paid in a private villa in Bali, Indonesia (Sep 2025)",
            "Entrepreneur “X” Factor Exchange program / Up to GBP 2000 sponsorship.",
            "The Leadership Game (6 sessions)",
            "Wealth Building Strategies with a professional global wealth builder.",
            "Soul-mate coaching for 6 sessions with a highly-qualified global coach",
            "DISC Code and report interpretation with a senior DISC Consultant.",
            "Limiting beliefs coaching for 6 sessions.",
            "Fundraising",
        ],
        ctaLabel: "Book now",
    },
];

function ContactInquiryForm() {
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        phone: "",
        subject: "",
        question: "",
    });
    const [submitted, setSubmitted] = useState(false);

    function updateField(field, value) {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setSubmitted(false);
    }

    function handleSubmit(event) {
        event.preventDefault();
        setSubmitted(true);
    }

    return (
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
            {submitted && <p className="booking-success">Thank you. Your message has been received.</p>}
            <button type="submit" className="btn btn-primary cursor-pointer">
                Submit
            </button>
        </form>
    );
}

function Layout({ children }) {
    const location = useLocation();

    return (
        <div className="page">
            <header className="topbar">
                <div className="container topbar-inner">
                    <a className="brand" href="/" aria-label="NeoSpace">
                        <img src="/assets/neospace-logo.png" alt="NeoSpace logo" />
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
                                        if (item.label === "Giving Back") {
                                            const active =
                                                location.pathname === "/" && location.hash === "#giving-back";
                                            return `nav-link ${active ? "active" : ""}`;
                                        }
                                        return `nav-link ${isActive ? "active" : ""}`;
                                    }}
                                >
                                    {item.label}
                                </NavLink>
                            );
                        })}
                    </nav>
                  
                </div>
            </header>
            <main>{children}</main>
            <footer className="footer">
                <div className="container footer-inner">
                    <p>NeoSpace Leadership Global</p>
                    <p>Copyright NeoSpace Global 2025.</p>
                </div>
            </footer>
        </div>
    );
}

function HomePage() {
    const location = useLocation();
    const query = useMemo(() => new URLSearchParams(location.search), [location.search]);
    const paymentResult = query.get("payment");
    const [showHomePopup, setShowHomePopup] = useState(false);
    const [homePopupMessage, setHomePopupMessage] = useState("");

    useEffect(() => {
        if (location.hash !== "#giving-back") {
            return;
        }
        const timer = window.setTimeout(() => {
            document.getElementById("giving-back")?.scrollIntoView({ behavior: "smooth" });
        }, 0);
        return () => window.clearTimeout(timer);
    }, [location.pathname, location.hash]);

    useEffect(() => {
        if (paymentResult === "success") {
            setHomePopupMessage("Payment received. Your appointment is confirmed.");
            setShowHomePopup(true);
        } else if (paymentResult === "cancelled") {
            setHomePopupMessage("Payment was cancelled. Please book again when ready.");
            setShowHomePopup(true);
        }
    }, [paymentResult]);

    function closeHomePopup() {
        setShowHomePopup(false);
        const url = new URL(window.location.href);
        url.searchParams.delete("payment");
        url.searchParams.delete("session_id");
        window.history.replaceState({}, "", `${url.pathname}${url.search}${url.hash}`);
    }

    return (
        <Layout>
            <section className="hero-wrap">
                <div className="hero container">
                    <p className="eyebrow">NeoSpace Leadership Global</p>
                    <h1>Personal and professional growth converge here.</h1>
                    <p className="lead">
                        Founded by Raouda Sakour, NeoSpace helps leaders, entrepreneurs, and family businesses unlock their next level
                        through coaching, transformational programs, and strategic guidance.
                    </p>
                    <div className="hero-actions">
                        <Link className="btn btn-primary" to="/contact">
                            Contact Us
                        </Link>
                        <a className="btn btn-ghost" href="/programs">
                            View Programs
                        </a>
                    </div>
                </div>
            </section>
            <section className="container section intro-section">
                <p className="eyebrow">About NeoSpace</p>
                <h2>Welcome to NeoSpace, where personal and professional growth converge.</h2>
                <p className="lead">
                    Founded by Raouda Sakour in August 2023, NeoSpace helps individuals and organizations unlock potential and reach
                    new heights—through coaching, leadership development, and holistic growth. We guide you past barriers, clarify
                    meaningful goals, and build the skills to succeed as your partners in progress, not just a service.
                </p>
                <p className="lead">
                    Explore our services and programs, and step into a space where transformation begins.
                </p>
            </section>
            <section className="container section our-services-section">
                <p className="eyebrow">Our Services</p>
                <h2>Methodologies that move you forward</h2>
                <p className="lead">
                    At NeoSpace we apply proven methodologies as catalysts for change—so individuals and organizations can adapt, solve
                    what matters most with clarity and foresight, and thrive in a shifting landscape. We work with results in mind,
                    walk with you to breakthrough, and help already-successful leaders reach the next level with new ways of thinking.
                </p>
                <div className="services-cards">
                    {services.map((item) => (
                        <article className="card" key={item.title}>
                            <img src={item.image} alt={item.title} className="service-card-image" />
                            <h3>{item.title}</h3>
                            <p>{item.body}</p>
                        </article>
                    ))}
                </div>
            </section>
            <section className="impact-band" id="giving-back">
                <div className="container impact-inner">
                    <p className="eyebrow">Giving Back</p>
                    <h2>Empowering Through Social Responsibility</h2>
                    <p>
                        At NeoSpace, we believe in making a difference. That&apos;s why XX% of the net profit from our services goes
                        toward educational initiatives that foster growth. By choosing NeoSpace, you become a part of this change.
                    </p>
                    <a className="btn btn-primary impact-band-cta" href="mailto:raouda@neospaceglobal.com">
                        Get in touch
                    </a>
                </div>
            </section>
            <section className="container section founder-section">
                <div className="founder-photo" />
                <article className="card founder-card">
                    <p className="eyebrow">Our Founder: Raouda Sakour</p>
                    <h3>Executive leadership with global perspective.</h3>
                    <p>
                        Raouda is a distinguished executive with 26+ years across sales, business development, consulting, and human
                        capital development. Fluent in Arabic, English, and Mandarin, she has held leadership roles at Cognizant, Capgemini,
                        Accenture, and Oracle Cloud Infrastructure.
                    </p>
                    <h4 className="founder-card-subhead">
                        <a href="https://www.maxwellleadership.com/" target="_blank" rel="noopener noreferrer">
                            Maxwell Leadership
                        </a>
                    </h4>
                    <p>
                        Through the John Maxwell Leadership Certified Program, Raouda connects clients to Maxwell enterprise content—including
                        the Maxwell Method of Speaking, Selling, Coaching, and Leadership. As a certified Coach, Speaker, and Leadership
                        Game Facilitator, she helps leaders strengthen skills, navigate challenges, and lift team performance.
                    </p>
                    <h4 className="founder-card-subhead">
                        <a href="https://www.circleofexcellence.biz/" target="_blank" rel="noopener noreferrer">
                            Circle of Excellence
                        </a>
                    </h4>
                    <p>
                        Her work with Circle of Excellence reflects a drive to maximize human potential: a global community in 50+ countries
                        offering entrepreneurs tools for prosperity, freedom, and purpose through education, technology, events, publications,
                        coaching, and consulting.
                    </p>
                </article>
            </section>
            <section className="container section">
                <article className="card cta-card">
                    <p className="eyebrow">Get In Touch</p>
                    <h3>Start a conversation about your next chapter.</h3>
                    <p>Have questions, comments, or inquiries? We would love to hear from you and help map your growth path.</p>
                    <Link className="btn btn-primary" to="/contact">
                        Book a Discovery Call
                    </Link>
                </article>
            </section>
            {showHomePopup && (
                <div className="booking-success-overlay" role="dialog" aria-modal="true" aria-label="Payment update">
                    <div className="booking-success-modal">
                        <h3>{paymentResult === "success" ? "Success" : "Payment update"}</h3>
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

function ServicesPage() {
    return (
        <Layout>
            <section className="container section services-page">
                <p className="eyebrow">Our Services</p>
                <h2>Transformative experiences that inspire conscious leadership.</h2>
                <p className="lead">
                    At Neospace, we are dedicated to creating transformative experiences that inspire conscious leadership and
                    meaningful impact. Our services are designed to foster deep, authentic connections through personalized coaching,
                    thought-provoking workshops, and innovative events.
                </p>
                <p className="lead">
                    Raouda's unique approach blends wisdom, empathy, and strategic insight to help individuals and organizations
                    unlock their potential and make a lasting difference. Whether it's through intimate one-on-one sessions or dynamic
                    group settings, Neospace is a haven for growth, where each conversation ignites new possibilities for personal and
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
                        Our Programs
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
    return (
        <Layout>
            <section className="programs-hero">
                <div className="container">
                    <h1>Our Programs</h1>
                </div>
            </section>
            <section className="container section programs-intro">
                <p className="lead">
                    Explore our programs, join us in making a difference, and dive into a wealth of content that inspires, educates, and
                    fuels your pursuit of greatness.
                </p>
                <div className="programs-stack">
                    {programs.map((program) => (
                        <article className="card program-card" key={program.name}>
                            <div className={`program-image ${program.key}`} />
                            <div className="program-content">
                                <p className="duration">{program.duration}</p>
                                {program.kicker ? <p className="program-kicker">{program.kicker}</p> : null}
                                <h3>{program.name}</h3>
                                <p className="program-details">{program.details}</p>
                                <ul>
                                    {program.points.map((point) => (
                                        <li key={point}>{point}</li>
                                    ))}
                                </ul>
                                <a className="btn btn-dark" href={`/booking?plan=${program.key}`}>
                                    {program.ctaLabel ?? "Apply now"}
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
    const paidPlanLabels = {
        "twelve-weeks": "12 Weeks Commitment",
        "six-months": "6 Months Commitment",
        "one-year": "1 Year Commitment",
    };
    const isPaidProgram = Boolean(selectedProgramPlan && paidPlanLabels[selectedProgramPlan]);

    const timeSlots = useMemo(() => {
        const out = [];
        for (let mins = 9 * 60; mins <= 18 * 60; mins += 30) {
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
                        You selected <strong>{paidPlanLabels[selectedProgramPlan]}</strong>. After submitting this form, you will be
                        redirected to Stripe to complete payment.
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

function App() {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/" element={<HomePage />} />
                <Route path="/services" element={<ServicesPage />} />
                <Route path="/programs" element={<ProgramsPage />} />
                <Route path="/booking" element={<BookingPage />} />
                <Route path="/contact" element={<ContactPage />} />
            </Routes>
        </BrowserRouter>
    );
}

createRoot(document.getElementById("app")).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
