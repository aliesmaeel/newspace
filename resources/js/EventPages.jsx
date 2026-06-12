import React, { useEffect, useState } from "react";
import { Link, useNavigate, useParams, useSearchParams } from "react-router-dom";
import { apiFetch } from "./apiClient";
import { useAuth } from "./AuthContext";

export function EventsPage({ Layout }) {
    const [events, setEvents] = useState([]);

    useEffect(() => {
        async function load() {
            const { data } = await apiFetch("/api/events");
            setEvents(Array.isArray(data?.events) ? data.events : []);
        }
        load();
    }, []);

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Events</p>
                <h2>Upcoming events</h2>
                <p className="lead">Register for an event. Your first event is free; after that, payment is required.</p>
                <div className="programs-stack">
                    {events.map((event) => (
                        <article className="card program-card" key={event.slug}>
                            {event.image_url ? (
                                <div
                                    className="program-image"
                                    style={{
                                        backgroundImage: `linear-gradient(to bottom, rgba(12, 11, 10, 0.15), rgba(12, 11, 10, 0.4)), url("${event.image_url}")`,
                                    }}
                                />
                            ) : null}
                            <div className="program-content">
                                <p className="duration">
                                    {event.location_label || (event.location_type === "virtual" ? "Virtual" : "In person")}
                                    <span className="event-meta-sep"> · </span>
                                    {event.price_label}
                                </p>
                                <h3>{event.title}</h3>
                                <p className="program-details">
                                    {event.starts_at
                                        ? new Date(event.starts_at).toLocaleString("en-GB", {
                                              dateStyle: "medium",
                                              timeStyle: "short",
                                          })
                                        : ""}
                                </p>
                                <Link className="btn btn-dark" to={`/events/${event.slug}`}>
                                    View event
                                </Link>
                            </div>
                        </article>
                    ))}
                    {events.length === 0 && <p className="lead">No upcoming events yet.</p>}
                </div>
            </section>
        </Layout>
    );
}

export function EventDetailPage({ Layout }) {
    const { slug } = useParams();
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const { user, loading: authLoading } = useAuth();
    const [event, setEvent] = useState(null);
    const [promoCode, setPromoCode] = useState("");
    const [error, setError] = useState("");
    const [message, setMessage] = useState("");
    const [submitting, setSubmitting] = useState(false);

    const loadEvent = React.useCallback(async () => {
        const { data } = await apiFetch(`/api/events/${slug}`);
        setEvent(data?.event ?? null);
    }, [slug]);

    useEffect(() => {
        if (authLoading) {
            return;
        }
        loadEvent();
    }, [authLoading, loadEvent]);

    useEffect(() => {
        if (searchParams.get("registration") === "success") {
            setMessage("Payment received. You are registered for this event!");
            if (!authLoading) {
                loadEvent();
            }
        }
    }, [searchParams, authLoading, loadEvent]);

    async function handleRegister() {
        if (!user) {
            navigate(`/register?redirect=/events/${slug}`);
            return;
        }
        setSubmitting(true);
        setError("");
        try {
            const { response, data } = await apiFetch(`/api/events/${slug}/register`, {
                method: "POST",
                body: JSON.stringify({
                    promo_code: promoCode || null,
                    return_base_url: window.location.origin,
                }),
            });
            if (!response.ok) {
                throw new Error(data?.message || "Could not register.");
            }
            if (data.checkout_url) {
                window.location.href = data.checkout_url;
                return;
            }
            setMessage(data.message || "You are registered!");
            await loadEvent();
        } catch (e) {
            setError(e.message);
        } finally {
            setSubmitting(false);
        }
    }

    if (!event) {
        return (
            <Layout>
                <section className="container section">
                    <p className="lead">Loading event…</p>
                </section>
            </Layout>
        );
    }

    const registered = event.user_registration?.status === "confirmed";
    const paid = event.user_registration?.payment_status === "paid";
    const locationLabel =
        event.location_label || (event.location_type === "virtual" ? "Virtual" : "In person");
    const isVirtual = event.location_type === "virtual";

    return (
        <Layout>
            <section className="container section">
                <p className="eyebrow">Event</p>
                <h2>{event.title}</h2>
                <p className="lead">
                    <span className={`event-location-badge event-location-badge--${event.location_type || "physical"}`}>
                        {locationLabel}
                    </span>
                    <span className="event-meta-sep"> · </span>
                    {event.price_label}
                </p>
                {event.image_url ? (
                    <img src={event.image_url} alt={event.title} className="service-card-image" style={{ marginBottom: "1rem" }} />
                ) : null}
                {event.description ? (
                    <div className="program-details" dangerouslySetInnerHTML={{ __html: event.description }} />
                ) : null}
                {event.location_type === "physical" && event.address ? (
                    <p className="lead">
                        <strong>Location:</strong> {event.address}
                        {event.map_url ? (
                            <>
                                {" "}
                                <a href={event.map_url} target="_blank" rel="noopener noreferrer">
                                    View on map
                                </a>
                            </>
                        ) : null}
                    </p>
                ) : null}
                {isVirtual && event.virtual_link ? (
                    <p className="lead">
                        <strong>Meeting link:</strong>{" "}
                        <a href={event.virtual_link} target="_blank" rel="noopener noreferrer" style={{ color: "#0057ff" }}>
                            Join online
                        </a>
                    </p>
                ) : null}
                {isVirtual && !event.virtual_link && event.has_virtual_meeting ? (
                    <p className="lead event-meeting-hint">
                        {registered && !paid
                            ? "Your payment is being confirmed. Refresh this page in a moment, or contact support if the meeting link does not appear."
                            : "The meeting link will appear here after you register and complete payment (or use a free registration)."}
                    </p>
                ) : null}
                {message && <p className="booking-success">{message}</p>}
                {error && <p className="booking-error">{error}</p>}
                {registered ? (
                    <>
                        <p className="booking-success">You are registered for this event.</p>
                        {isVirtual && paid && !event.virtual_link && event.has_virtual_meeting ? (
                            <p className="lead event-meeting-hint">Meeting link is not available yet. Please contact support.</p>
                        ) : null}
                    </>
                ) : (
                    <>
                        {!user && (
                            <p className="lead">
                                <Link to={`/register?redirect=/events/${slug}`}>Register</Link> or{" "}
                                <Link to={`/login?redirect=/events/${slug}`}>log in</Link> to attend.
                            </p>
                        )}
                        {user && !event.has_attended_before && (
                            <p className="lead">Your first event registration is free.</p>
                        )}
                        <div className="booking-form" style={{ maxWidth: "28rem", marginTop: "1rem" }}>
                            <input
                                type="text"
                                placeholder="Promo code (optional)"
                                value={promoCode}
                                onChange={(e) => setPromoCode(e.target.value)}
                            />
                            <button type="button" className="btn btn-primary" disabled={submitting} onClick={handleRegister}>
                                {submitting ? "Please wait…" : user ? "Register for event" : "Register / log in to attend"}
                            </button>
                        </div>
                    </>
                )}
            </section>
        </Layout>
    );
}
