import { Outlet } from "react-router-dom";
import Navbar from "../components/Navbar";
import { Component } from "react";

export default class MainLayout extends Component {
  render(): React.ReactNode {
    return (
      <>
        <Navbar />
        <div className="container mt-[150px]">
          <Outlet />
        </div>
      </>
    );
  }
}
