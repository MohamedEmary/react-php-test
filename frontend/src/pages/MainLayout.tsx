import { Outlet } from "react-router-dom";
import Navbar from "../components/Navbar";
import React from "react";

export default class MainLayout extends React.Component {
  render(): React.ReactNode {
    return (
      <div className="container">
        <Navbar />
        <Outlet />
      </div>
    );
  }
}
