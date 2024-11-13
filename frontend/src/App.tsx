import { Component } from "react";
import { BrowserRouter } from "react-router-dom";
import Navbar from "./components/Navbar";

export default class App extends Component {
  render(): React.ReactNode {
    return (
      <BrowserRouter>
        <div className="container mx-auto">
          <Navbar />
        </div>
      </BrowserRouter>
    );
  }
}
