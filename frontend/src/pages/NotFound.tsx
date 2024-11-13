import React from "react";
import { Link } from "react-router-dom";

export default class NotFound extends React.Component {
  render() {
    return (
      <div className="h-screen flex flex-col items-center justify-center px-4">
        <h1 className="text-[42px] mb-4">404</h1>
        <p className="text-[24px] mb-8 text-gray-600">
          Sorry, the page you're looking for cannot be found
        </p>
        <Link
          to="/"
          className="px-6 py-3 bg-green-700 text-white hover:bg-green-600 transition-colors"
        >
          Back to Homepage
        </Link>
      </div>
    );
  }
}
