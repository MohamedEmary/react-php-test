import { Component, createContext } from "react";

interface UserContextType {
  userId: number | null;
  setUserId: (id: number) => void;
}

interface UserContextProps {
  children: React.ReactNode;
}

export const userContext = createContext<UserContextType | null>(null);

export default class UserContextProvider extends Component<UserContextProps> {
  state = {
    userId: null,
  };

  setUserId = (id: number) => {
    this.setState({ userId: id });
    localStorage.setItem("userId", id.toString());
  };

  componentDidMount() {
    const savedUserId = localStorage.getItem("userId");
    if (savedUserId) {
      this.setState({ userId: savedUserId });
    }
  }

  render(): React.ReactNode {
    return (
      <userContext.Provider
        value={{
          userId: this.state.userId,
          setUserId: this.setUserId,
        }}
      >
        {this.props.children}
      </userContext.Provider>
    );
  }
}
