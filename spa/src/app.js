import { h, render} from "preact";

const App = () => {
    return (
        <div>
            Hello world!
        </div>
    )
}

render(<App />, document.getElementById("app"));
