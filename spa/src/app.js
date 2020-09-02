import React from "preact/compat";
import { h, render} from "preact";
import Router, {Link} from "preact-router";

import Home from "./pages/home";
import Conference from "./pages/conference";

const App = () => {
    return (
        <div>
            <header>
                <Link href="/">Home</Link>
                <br/>
                <Link href="/conference/brasilia-2021">Brasilia 2021</Link>
            </header>

            <Router>
                <Home path="/" />
                <Conference path="/conference/:slug" />
            </Router>
        </div>
    )
}

render(<App />, document.getElementById("app"));
