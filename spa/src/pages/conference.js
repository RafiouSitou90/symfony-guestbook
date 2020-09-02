import { h } from "preact";
import React from "preact/compat";
import { useState, useEffect } from "preact/hooks";

import { findComments } from "../api/api";
import Comment from "./comment";


const Conference = ({conferences, slug}) => {
    const conference = conferences.find(conference => conference.slug ===  slug);
    const [comments, setComments] = useState(null);

    useEffect(() => {
        findComments(conference).then(comments => setComments(comments));
    }, [slug]);

    return (
        <div className="p-3">
            <h4>{conference.city} {conference.year}</h4>
            <Comment comments={comments} />
        </div>
    )
};

export default Conference;
